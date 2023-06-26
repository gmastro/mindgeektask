<?php
declare(strict_types=1);

namespace Tests\Unit\Customizations\Adapters;

use App\Customizations\Adapters\CurlDownloadAdapter;
use App\Customizations\Components\CurlComponent;
use App\Customizations\Components\FileOpenComponent;
use App\Customizations\Traits\ErrorCodeTrait;
use App\Customizations\Traits\FilenameTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\TestCase;

#[CoversClass(CurlDownloadAdapter::class)]
#[UsesClass(CurlComponent::class)]
#[UsesClass(FileOpenComponent::class)]
#[UsesClass(ErrorCodeTrait::class)]
#[UsesClass(FilenameTrait::class)]
class CurlDownloadAdapterTest extends TestCase
{
    public static function providerUrls(): array
    {
        return [
            'hamilton-link-filename'        => ["https://www.hamiltonstaracademy.com/images/frontpage/portfolio/fullsize/Screenshot1.png", "", "Screenshot1.png"],
            'hamilton-provided-filename'    => ["https://www.hamiltonstaracademy.com/images/frontpage/portfolio/fullsize/Screenshot1.png", "/moufa.png", "moufa.png"],
            "place-hold-it-png"             => ["https://place-hold.it/244x344/666321/123666.png&text=lorem-ipsum&bold&italic&fontsize=11", "", "123666.png&text=lorem-ipsum&bold&italic&fontsize=11.png"],
            "place-hold-it-jpg"             => ["https://place-hold.it/244x344/666321/123666.jpg&text=lorem-ipsum&bold&italic&fontsize=11", "", "123666.jpg&text=lorem-ipsum&bold&italic&fontsize=11.jpg"],
            "json-sample"                   => ["https://microsoftedge.github.io/Demos/json-dummy-data/64KB.json", "/microsoftedge-64k.json", "microsoftedge-64k.json"],
            'example'                       => ["https://example.com", "", \md5("https://example.com/") . ".html"],
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
    #[DataProvider('providerUrls')]
    public function test_success_constructor_with_curl(string $url, string $storagePath, string $expectedFilename): void
    {
        $directory = config('filesystems.disks.downloads.root');
        $component = $this->urlToCurlComponent($url);

        $sut = new CurlDownloadAdapter($component, $directory . $storagePath);
        $response = $sut->execute();

        $this->assertTrue($response);
        $this->assertTrue($sut->isDownloaded());

        $filename = $sut->getFilename();
        $this->assertStringEndsWith($expectedFilename, $filename);
        $this->assertFileExists($filename);
        $this->assertGreaterThan(0, \filesize($filename));
        $this->assertStringNotEqualsFileIgnoringCase($filename, "");

        \unlink($filename);
    }
}
