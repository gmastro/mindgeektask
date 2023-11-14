<?php
declare(strict_types=1);

namespace Tests\Unit\Customizations\Traits;

use App\Customizations\Components\CurlComponent;
use App\Customizations\Traits\FilenameTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\TestCase;
use UnhandledMatchError;

#[CoversClass(FilenameTrait::class)]
#[UsesClass(CurlComponent::class)]
class FilenameTraitTest extends TestCase
{
    public static function providerCodes(): array
    {
        return \array_map(fn($w) => [$w], fake()->words(5));
    }

    #[Group('success')]
    #[Group('setter')]
    #[DataProvider('providerCodes')]
    public function test_success_setter_getter(string $filename): void
    {
        $sut = new class{
            use FilenameTrait {
                setFilename as public;
                
            }
        };
        $sut->setFilename($filename);
        $this->assertSame($filename, $sut->getFilename());
    }

    public static function providerLinks(): array
    {
        return [
            'hamilton-link-filename'        => ["https://www.hamiltonstaracademy.com/images/frontpage/portfolio/fullsize/Screenshot1.png", "Screenshot1.png"],
            "place-hold-it-png"             => ["https://place-hold.it/244x344/666321/123666.png&text=lorem-ipsum&bold&italic&fontsize=11", "123666.png&text=lorem-ipsum&bold&italic&fontsize=11.png"],
            "place-hold-it-jpg"             => ["https://place-hold.it/244x344/666321/123666.jpg&text=lorem-ipsum&bold&italic&fontsize=11", "123666.jpg&text=lorem-ipsum&bold&italic&fontsize=11.jpg"],
            'example'                       => ["https://example.com", \md5("https://example.com/") . ".html"],
            'google'                        => ["https://www.google.com/", \md5("https://www.google.com/") . ".html"],
        ];
    }

    public static function providerSamples(): array
    {
        return [
            "sample-json"   => ["https://microsoftedge.github.io/Demos/json-dummy-data/64KB.json", "64KB.json"],
            // haven't found a sample link yet containing application/rdf+xml
            'sample-rdf'    => ['https://web.resource.org/rss/1.0/schema.rdf', 'schema.rdf.xml'],
            'sample-atom'   => ['www.intertwingly.net/blog/index.atom', 'index.atom'],
            'sample-csv'    => ['https://cdn.wsform.com/wp-content/uploads/2020/06/industry.csv', 'industry.csv'],
            "sample-xml"    => ['https://www.learningcontainer.com/wp-content/uploads/2020/03/Sample-XML-Files.xml', 'Sample-XML-Files.xml'],
        ];
    }

    public static function providerUrls(): array
    {
        return self::providerLinks() + self::providerSamples();
    }

    #[Group('success')]
    #[Group('from-remote-stream')]
    #[DataProvider('providerUrls')]
    public function test_success_from_remote_stream(string $url, string $expected): void
    {
        $sut = new class{
            use FilenameTrait {
                fromRemoteStream as public;
            }
        };
        
        $component = new CurlComponent([
            CURLOPT_URL             => $url,
            CURLOPT_NOBODY          => true,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_FILETIME        => true,
        ]);
        $component->execute();

        $this->assertSame($expected, $sut->fromRemoteStream($component));
        $this->assertNull($sut->getFilename());
    }

    #[Group('exception')]
    #[Group('from-remote-stream')]
    public function test_exception_from_remote_stream(): void
    {
        $this->expectException(UnhandledMatchError::class);
        $sut = new class{
            use FilenameTrait {
                fromRemoteStream as public;
            }
        };

        $component = new CurlComponent([
            CURLOPT_URL             => "https://getsamplefiles.com/download/ogg/sample-4.ogg",
            CURLOPT_NOBODY          => true,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_FILETIME        => true,
        ]);
        $component->execute();
        $sut->fromRemoteStream($component);
    }
}