<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Thumbnails extends Model
{
    use HasFactory;

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
        'url',
        'width',
        'height',
        'media',
    ];

    /**
     * Relation
     *
     * Just for clarification, the actual relation is one-to-many
     * One `DownloadedFiles` - Many `Thumbnails`
     * It will return the left hand side value
     *
     * @access  public
     * @return  BelongsTo
     */
    public function downloaded(): BelongsTo
    {
        return $this->belongsTo(DownloadedFiles::class, 'downloaded_file_id');
    }

    /**
     * Relation
     *
     * Just for clarification, the actual relation is one-to-many
     * One `RemoteFeeds` - Many `Thumbnails`
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
     * It will return the left hand side value (collection)
     *
     * @access  public
     * @return  BelongsToMany
     */
    public function pornstars(): BelongsToMany
    {
        return $this->belongsToMany(Pornstars::class, 'pornstars_thumbnails', 'thumbnail_id', 'pornstar_id')
            ->withTimestamps();
    }
}
