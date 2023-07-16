<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Collection;

class Pornstars extends Model
{
    use HasFactory;

    /**
     * {@inheritdoc}
     */
    public $incrementing = false;

    /**
     * {@inheritdoc}
     */
    protected static function boot()
    {
        parent::boot();
        Model::preventSilentlyDiscardingAttributes();
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    protected $appends = [
        'downloads'
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'attributes'    => AsArrayObject::class,
        'stats'         => AsArrayObject::class,
        'aliases'       => AsArrayObject::class,
    ];

    /**
     * Relation
     *
     * Just for clarification, the actual relation is one-to-many
     * One `RemoteFeeds` - Many `Pornstars`
     * It will return the left hand side value
     *
     * @access  public
     * @return  BelongsTo
     */
    public function remoteFeeds(): BelongsTo
    {
        return $this->belongsTo(RemoteFeeds::class, 'remote_feed_id');
    }

    /**
     * Relation
     *
     * Just for clarification, the actual relation is many-to-many
     * Many `Pornstars` - Many `Thumbnails`
     * For this to happen there is a pivot table and a model `PornstarsThumbnails`.
     * It will return the right hand side value (collection)
     *
     * @access  public
     * @return  BelongsToMany
     */
    public function thumbnails(): BelongsToMany
    {
        return $this->belongsToMany(Thumbnails::class, 'pornstars_thumbnails', 'pornstar_id', 'thumbnail_id')
            ->withTimestamps();
    }

    /**
     * Attribute
     *
     * Hardcoded attribute right after invoking thumbnails relation.
     * In return it will receive the a `DownloadedFiles` model from the thumbnails.
     * No, it will not work with BelongsToManyThrough.
     *
     * @access  public
     * @return  Collection
     */
    public function getDownloadsAttribute(): Collection
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
