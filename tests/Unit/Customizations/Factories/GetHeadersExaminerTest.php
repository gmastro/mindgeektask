<?php
declare(strict_types=1);

namespace Tests\Unit\Customizations\Factories;

use App\Customizations\Factories\GetHeadersExaminer;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

/**
 * @covers  App\Customizations\Factories\GetHeadersExaminer
 * @uses    Carbon\Carbon
 */
class GetHeadersExaminerTest extends TestCase
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
     * @covers          App\Customizations\Factories\GetHeadersExaminer::build
     * @covers          App\Customizations\Factories\GetHeadersExaminer::isValid
     * @covers          App\Customizations\Factories\GetHeadersExaminer::getStatusCode
     * @covers          App\Customizations\Factories\GetHeadersExaminer::getContentType
     * @covers          App\Customizations\Factories\GetHeadersExaminer::getLastModified
     */
    public function test_get_headers_rest_head_success(
        string $url,
        int $statusCode,
        string $contentType,
        string $dateTime
    ): void {
        $sut = new GetHeadersExaminer($url);
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
     * @covers          App\Customizations\Factories\GetHeadersExaminer::build
     * @covers          App\Customizations\Factories\GetHeadersExaminer::isValid
     */
    public function test_get_headers_failure(string $url): void
    {
        $sut = new GetHeadersExaminer($url);
        $this->assertFalse($sut->isValid());
        $this->assertEmpty($sut->getStatusCode());
    }

    /**
     * Redirect
     *
     * @group           constructor
     * @group           redirect
     * @covers          App\Customizations\Factories\GetHeadersExaminer::build
     * @covers          App\Customizations\Factories\GetHeadersExaminer::isValid
     * @covers          App\Customizations\Factories\GetHeadersExaminer::getStatusCode
     * @covers          App\Customizations\Factories\GetHeadersExaminer::getContentType
     */
    public function test_get_headers_redirect(): void
    {
        $sut = new GetHeadersExaminer('http://google.com');
        $this->assertTrue($sut->isValid());
        $this->assertEquals(200, $sut->getStatusCode());
        $this->assertStringContainsString('text/html', $sut->getContentType());
    }
}
