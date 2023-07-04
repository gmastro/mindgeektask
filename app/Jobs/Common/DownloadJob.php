<?php

declare(strict_types=1);

namespace App\Jobs\Common;

use App\Customizations\Composites\Composite;
use App\Customizations\Composites\DownloadComponent;
use App\Customizations\Composites\DownloadedFilesCreateComponent;
use App\Customizations\Composites\ExamineComponent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class DownloadJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $timeout = 360;

    public $failOnTimeout = false;

    public function uniqueId(): string
    {
        return $this->source;
    }

    /**
     * Magic Constuctor
     *
     * Each download process requires the following
     */
    public function __construct(
        private Model $model,
        private string $disk = 'downloads',
        private ?string $source = null
    ) {
        $this->model->withoutRelations();
        $this->source ??= $model->source ?? 'no-source-just-fail';
    }

    public function handle(): bool
    {
        $composite = new Composite(collect([
            new ExamineComponent(),
            new DownloadComponent(),
            new DownloadedFilesCreateComponent(),
        ]), (object) [
            'source'    => $this->source,
            'model'     => $this->model,
            'disk'      => $this->disk,
        ]);

        return $composite->execute();
    }

    public function failed(Throwable $e)
    {
        Log::error($e->getMessage(), ['model' => $this->model]);
    }
}
