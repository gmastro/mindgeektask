<?php
declare(strict_types=1);

namespace Tests\Unit\Customizations\Adapters;

use App\Customizations\Adapters\CurlDownloadAdapter;
use App\Customizations\Components\CurlComponent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\TestCase;

#[CoversClass(CurlDownloadAdapter::class)]
#[UsesClass(CurlComponent::class)]
#[UsesClass(FOpenComponent::class)]
class DownloadAdapterTest extends TestCase
{
    public static function providerUrls(): array
    {
        return [
            'hamilton'  => ["https://www.hamiltonstaracademy.com/images/frontpage/portfolio/fullsize/Screenshot1.png"],
            // 'example'   => ["https://example.com"],
            // 'pornhub'   => ["https://www.pornhub.com/files/json_feed_pornstars.json"],
            // 'google'    => ["https://www.google.com"],
        ];
    }

    private function urlToCurlComponent(string $url): CurlComponent
    {
        $component = new CurlComponent([
            CURLOPT_URL             => $url,
            CURLOPT_NOBODY          => true,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_FILETIME        => true,
        ]);
        $component->execute();
        return $component;
    }

    #[Group('constructor')]
    #[Group('success')]
    #[Group('curl')]
    #[Group('fopen')]
    #[DataProvider('providerUrls')]
    public function test_success_constructor_with_curl_and_file_open(string $url): void
    {
        $component = $this->urlToCurlComponent($url);

        $sut = new CurlDownloadAdapter(
            $component,
            config('filesystems.disks.downloads')['root']
        );
        $this->assertTrue($sut->execute());
        $this->assertTrue($sut->isDownloaded());
    }
}
