<?php
declare(strict_types=1);

namespace Tests\Unit\Customizations\Components;

use App\Customizations\Components\CurlComponent;
use App\Customizations\Components\FileOpenComponent;
use App\Customizations\Traits\ErrorCodeTrait;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

#[CoversClass(CurlComponent::class)]
#[UsesClass(FileOpenComponent::class)]
#[UsesClass(ErrorCodeTrait::class)]
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
        $sut->execute();
        $contents = $sut->getContents();
        $this->assertNotFalse($contents);
        $this->assertIsString($contents);
        $this->assertSame("", $contents);
        $this->assertSame(0, $sut->getErrorCode());
        
        $info = $sut->getInfo();
        $this->assertIsArray($info);
        $this->assertSame($statusCode, (int) $info[$sut::STATUS_CODE]);
        $this->assertSame($contentType, \explode(";", $info[$sut::CONTENT_TYPE])[0]);
    }

    #[Group('constructor')]
    #[Group('get')]
    #[Group('success')]
    #[DataProvider('providerUrlsSmall')]
    public function test_success_constructor_get(string $url): void
    {
        /**
         * @var FilesystemManager $storage
         */
        $storage = Storage::fake('moufa');
        $path = $storage->path('');
        $filename = \sprintf("%s/%s", $path, Uuid::uuid4()->toString());
        $out = new FileOpenComponent($filename);
        $out->execute();

        $sut = new CurlComponent(self::CURL_GET + [
            CURLOPT_URL     => $url,
            CURLOPT_FILE    => $out->getHandle(),
        ]);
        $sut->execute();
        unset($out);
        $this->assertSame(0, $sut->getErrorCode());

        $contents = $sut->getContents();
        $this->assertNotEmpty($contents);
        $this->assertTrue($contents);
        
        $info = $sut->getInfo();
        $this->assertSame(200, $info[$sut::STATUS_CODE]);
        $this->assertFileExists($filename);
        $this->assertSame((int) $info[$sut::CONTENT_LENGTH], (int) \filesize($filename));
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
        $sut->execute();
        $this->assertGreaterThan(0, $sut->getErrorCode());
        $this->assertFalse($sut->getContents());
        $this->assertNull($sut->getInfo());
    }
}
