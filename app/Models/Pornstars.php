<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Pornstars extends Model
{
    use HasFactory;

    public $increment = false;

    protected static function boot()
    {
        parent::boot();
        Model::preventSilentlyDiscardingAttributes();
    }

    public $fillable = [
        'id',
        'name',
        'link',
        'license',
        'attributes',
        'wlStatus',
        'attributes',
        'stats',
        'aliases',
    ];

    protected $appends = [
        'downloads'
    ];

    protected $casts = [
        'attributes'    => AsArrayObject::class,
        'stats'         => AsArrayObject::class,
        'aliases'       => AsArrayObject::class,
    ];

    public function remoteFeeds(): BelongsTo
    {
        return $this->belongsTo(RemoteFeeds::class);
    }

    public function thumbnails(): BelongsToMany
    {
        return $this->belongsToMany(Thumbnails::class, 'pornstars_thumbnails', 'pornstar_id', 'thumbnail_id')
            ->withTimestamps();
    }

    public function getDownloadsAttribute()
    {
        return $this->thumbnails->map(fn ($model) => [
            'url'       => $model->downloaded?->md5_hash,
            'hotlink'   => $model->url,
            'width'     => $model->width,
            'height'    => $model->height,
            'media'     => $model->media
        ]);
    }
}
