<?php

declare(strict_types=1);

use App\Customizations\Adapters\RedisFileCachingAdapter;
use App\Customizations\Composites\DownloadComponent;
use App\Customizations\Composites\DownloadedFilesCreateComponent;
use App\Customizations\Composites\ExamineComponent;
use App\Customizations\Composites\interfaces\InterfaceShare;
use App\Customizations\Traits\ShareTrait;
use App\Models\DownloadedFiles;
use Illuminate\Support\Facades\Redis;
use Tests\Fixtures\Generators\AbstractGenerator;

class DownloadGenerator extends AbstractGenerator
{
    /**
     * {@inheritdoc}
     */
    protected function isAllowedEnumerator(): void
    {
        if(false === ($this->enumerator instanceof EnumDownloadGenerator)) {
            throw new \InvalidArgumentException(\sprintf(
                "Allowed generator class %s",
                EnumDownloadGenerator::class
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function mapping(int $action): bool
    {
        $composite = match($action) {
            EnumDownloadGenerator::Examine->value => new ExamineComponent(),
            EnumDownloadGenerator::Download->value => new DownloadComponent(),
            EnumDownloadGenerator::Filesystem->value => new class implements InterfaceShare {
                use ShareTrait;

                public function execute(): bool
                {
                    // do something
                    return true;
                }
            },
            EnumDownloadGenerator::Database->value => new DownloadedFilesCreateComponent,
            EnumDownloadGenerator::Cache->value => new class implements InterfaceShare {
                use ShareTrait;

                public function execute(): bool
                {
                    Redis::pipeline(fn($pipe) => (new RedisFileCachingAdapter($pipe, DownloadedFiles::all()))->execute());
                    return true;
                }
            },
            default => false
        };

        if(false === $composite) {
            return false;
        }

        $this->order[] = $composite;
        return true;
    }
}