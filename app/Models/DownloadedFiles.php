<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DownloadedFiles extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected static function boot()
    {
        parent::boot();
        // use config to make this dynamic and get $this->app->isProduction() === false
        Model::preventSilentlyDiscardingAttributes();

        $md5Hash = function (DownloadedFiles $model): void {
            $filename = \implode('/', [config('filesystems.disks')[$model->disk]['root'], $model->filename]);
            // $model->fullpath = $filename;
            $model->md5_hash = \md5($filename);
            $model->filesize = \is_file($filename)
                ? (int) \filesize($filename)
                : 0;

            if($model->filesize === 0) {
                $model->deleted_at = Carbon::now();
                $model->is_cached = false;
            }
        };

        static::creating($md5Hash);
        static::updating($md5Hash);
    }

    protected $fillable = [
        'filename',
        'disk',
        'mime_type',
        'is_cached',
    ];

    public $guarded = [];

    public function remote_feeds(): HasMany
    {
        return $this->hasMany('remote_feeds', 'downloaded_file_id');
    }

    public function thumbnails(): HasMany
    {
        return $this->hasMany('thumbnails', 'downloaded_file_id');
    }
}
