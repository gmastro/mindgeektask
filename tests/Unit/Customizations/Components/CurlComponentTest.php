<?php
declare(strict_types=1);

namespace Tests\Unit\Customizations\Components;

use App\Customizations\Components\CurlComponent;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\DataProvider;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

/**
 * #[CoversClass(App\Customizations\Factories\CurlExaminer::class)]
 */
class CurlComponentTest extends TestCase
{
    const CURL_HEADER = [
        CURLOPT_NOBODY          => true,
        CURLOPT_RETURNTRANSFER  => true,
        CURLOPT_FILETIME        => true,
    ];

    const CURL_GET = [
        CURLOPT_RETURNTRANSFER  => false,
    ];

    public static function providerUrlsSmall(): array
    {
        return [
            'hamilton'  => ["https://www.hamiltonstaracademy.com//images/frontpage/portfolio/fullsize/Screenshot1.png"],
            'example'   => ["https://example.com"],
        ];
    }

    public static function providerUrlsLarge(): array
    {
        return [
            'pornhub'   => ["https://www.pornhub.com/files/json_feed_pornstars.json"],
            'google'    => ["https://google.com"],
        ];
    }

    public static function providerUrls(): array
    {
        return self::providerUrlsSmall()
             + self::providerUrlsLarge();
    }

    public static function providerResults(): array
    {
        return [
            'hamilton'  => [200, 'image/png'],
            'example'   => [200, 'text/html'],
            'pornhub'   => [200, 'application/json'],
            'google'    => [301, 'text/html'],
        ];
    }

    public static function providerUrlsResults(): array
    {
        return \array_map(fn($u, $r) => \array_merge($u, $r), self::providerUrls(), self::providerResults());
    }

    #[Group('constructor')]
    #[Group('header')]
    #[Group('success')]
    #[DataProvider('providerUrlsResults')]
    public function test_success_constructor_header(string $url, int $statusCode, string $contentType): void
    {
        $sut = new CurlComponent(self::CURL_HEADER + [CURLOPT_URL => $url]);
        $sut->run();
        $contents = $sut->getContents();
        $this->assertNotFalse($contents);
        $this->assertIsString($contents);
        $this->assertSame("", $contents);
        
        $info = $sut->getInfo();
        $this->assertIsArray($info);
        $this->assertSame($statusCode, (int) $info['http_code']);
        $this->assertSame($contentType, \explode(";", $info['content_type'])[0]);
    }

    #[Group('constructor')]
    #[Group('get')]
    #[Group('success')]
    #[DataProvider('providerUrlsSmall')]
    public function test_success_constructor_get(string $url): void
    {
        $uuid = Uuid::uuid4()->toString();
        $storage = config('filesystems.disks.downloads');
        $filename = \sprintf("%s/%s", $storage['root'], $uuid);
        $out = \fopen($filename, "wb");

        if($out === false) {
            $this->assertTrue(false);
            return;
        }

        $sut = new CurlComponent(self::CURL_GET + [
            CURLOPT_URL     => $url,
            CURLOPT_FILE    => $out,
        ]);
        $sut->run();
        \fclose($out);

        $contents = $sut->getContents();
        $this->assertNotEmpty($contents);
        $this->assertTrue($contents);
        
        $info = $sut->getInfo();
        $this->assertSame(200, $info['http_code']);
        $this->assertTrue(\is_file($filename));
        $this->assertSame((int) $info['download_content_length'], (int) \filesize($filename));
        \unlink($filename);
    }

    public static function providerNotUrl(): array
    {
        return [
            'not-url'   => ['fooBar'],
            'file'      => ['file://fooBar'],
            'foo-http'  => ['http://sub.i-am-not-supposed-to-be-any-domain-used.net/clients'],
            'foo-https' => ['https://sub.i-am-not-supposed-to-be-any-domain-used.net/clients'],
        ];
    }

    #[Group('constructor')]
    #[Group('header')]
    #[Group('failure')]
    #[DataProvider('providerNotUrl')]
    public function test_failure_constructor_header_invalid_url(string $url): void
    {
        $sut = new CurlComponent(self::CURL_HEADER + [CURLOPT_URL => $url]);
        $sut->run();
        $this->assertFalse($sut->getContents());
        $this->assertNull($sut->getInfo());
    }
}
