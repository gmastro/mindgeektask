<?php

declare(strict_types=1);

namespace App\Jobs\Common;

use App\Customizations\Composites\Composite;
use App\Customizations\Composites\DownloadComponent;
use App\Customizations\Composites\DownloadedFilesCreateComponent;
use App\Customizations\Composites\ExamineComponent;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use InvalidArgumentException;
use Throwable;

class DownloadJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use Batchable;

    /**
     * Attributes Property
     *
     * Data required for getting the content via structured composite
     *
     * @access  private
     * @var     array $attributes
     */
    private $attributes = [];

    public $timeout = 360;

    public $failOnTimeout = false;

    public function uniqueId(): string
    {
        return $this->source;
    }

    /**
     * Magic Constuctor
     *
     * Each download process will require some initialization data.
     * For reducing load when structuring a queue within a batch, it uses a callable data array
     *
     * @access  public
     * @param   Model $model
     * @param   string $disk **Default `'downloads'`**, storage name to place downloaded content
     * @param   string|null $source **Default `null`**, source of downloadable content if the model has no source
     *          property. This attribute takes priority over model's source
     */
    public function __construct(?Model $model, string $disk = 'downloads', private ?string $source = null)
    {
        $this->source ??= $model?->source;

        if($this->source === null) {
            throw new InvalidArgumentException(
                'Source property should be provided either via $model, or explicitely via $source'
            );
        }

        $this->attributes = [
            'source'    => $this->source,
            'disk'      => $disk,
            'model'     => $model
        ];

        if($model === null) {
            unset($this->attributes['model']);
        }
    }

    public function handle()
    {
        Redis::throttle('downloads')->allow(500)->every(60)->block(60)->then(
            fn() => (new Composite(
                collect([
                    new ExamineComponent(),
                    new DownloadComponent(),
                    new DownloadedFilesCreateComponent(),
                ]),
                (object) $this->attributes
            ))->execute()
        );
    }

    public function failed(Throwable $e)
    {
        Log::error($e->getMessage(), ['attributes' => $this->attributes]);
    }
}
