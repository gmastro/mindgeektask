<?php
declare(strict_types=1);

namespace Tests\Unit\Customizations\Factories;

use App\Customizations\Factories\CurlExaminer;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

/**
 * @covers  App\Customizations\Factories\CurlExaminer
 * @uses    Carbon\Carbon
 */
class CurlExaminerTest extends TestCase
{
    public static function providerUrls(): array
    {
        return [
            'pornhub'   => ["https://www.pornhub.com/files/json_feed_pornstars.json"],
            'hamilton'  => ["https://www.hamiltonstaracademy.com//images/frontpage/portfolio/fullsize/Screenshot1.png"],
        ];
    }

    public static function providerResults(): array
    {
        $now = Carbon::now()->toDateTimeString();
        return [
            'pornhub'   => [200, 'application/json', $now],
            'hamilton'  => [200, 'image/png', $now],
        ];
    }

    public static function providerUrlResults(): array
    {
        return \array_map(fn($u, $r) => \array_merge($u, $r), self::providerUrls(), self::providerResults());
    }

    /**
     * Get curl information from url
     *
     * @group           constructor
     * @dataProvider    providerUrlResults
     * @covers          App\Customizations\Factories\CurlExaminer::build
     * @covers          App\Customizations\Factories\CurlExaminer::isValid
     * @covers          App\Customizations\Factories\CurlExaminer::getStatusCode
     * @covers          App\Customizations\Factories\CurlExaminer::getContentType
     * @covers          App\Customizations\Factories\CurlExaminer::getLastModified
     */
    public function test_curl_getinfo_rest_head_success(
        string $url,
        int $statusCode,
        string $contentType,
        string $dateTime
    ): void {
        $sut = new CurlExaminer($url);
        $this->assertTrue($sut->isValid());
        $this->assertEquals($statusCode, $sut->getStatusCode());
        $this->assertEquals($contentType, $sut->getContentType());
        $this->assertIsString($sut->getLastModified());
        $this->assertNotEquals($dateTime, $sut->getLastModified());
    }

    public static function providerNotUrl(): array
    {
        return [
            'not-url'   => ['fooBar'],
            'file'      => ['file://fooBar'],
            'foo-url'   => ['https://sub.i-am-not-supposed-to-be-any-domain-used.net/clients'],
        ];
    }

    /**
     * Not valid urls
     *
     * @group           constructor
     * @group           fail
     * @dataProvider    providerNotUrl
     * @covers          App\Customizations\Factories\CurlExaminer::build
     * @covers          App\Customizations\Factories\CurlExaminer::isValid
     * @covers          App\Customizations\Factories\CurlExaminer::getStatusCode
     * @covers          App\Customizations\Factories\CurlExaminer::getContentType
     */
    public function test_curl_getinfo_failure(string $url): void
    {
        $sut = new CurlExaminer($url);
        $this->assertFalse($sut->isValid());
        $this->assertEquals(0, $sut->getStatusCode());
        $this->assertEmpty($sut->getContentType());
    }

    /**
     * Redirect
     *
     * @group           constructor
     * @group           redirect
     * @covers          App\Customizations\Factories\CurlExaminer::build
     * @covers          App\Customizations\Factories\CurlExaminer::isValid
     * @covers          App\Customizations\Factories\CurlExaminer::getStatusCode
     * @covers          App\Customizations\Factories\CurlExaminer::getContentType
     */
    public function test_curl_redirect(): void
    {
        $sut = new CurlExaminer('http://google.com');
        $this->assertFalse($sut->isValid());
        $this->assertEquals(301, $sut->getStatusCode());
        $this->assertStringContainsString('text/html', $sut->getContentType());
    }
}
