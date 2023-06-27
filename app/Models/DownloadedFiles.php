<?php
declare(strict_types=1);

namespace App\Models;

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

        $md5Hash = function(DownloadedFiles $model) {
            $model->md5_hash = \md5(\implode(
                '/',
                [config('filesystems.disks')[$model->disk]['root'], $model->filename]
            ));
        };

        static::creating($md5Hash);
        static::updating($md5Hash);
    }

    protected $fillable = [
        'filesize',
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
