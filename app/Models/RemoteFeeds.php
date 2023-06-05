<?php

namespace App\Models;

use App\Events\RemoteFeedDeleting;
use App\Events\RemoteFeedEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RemoteFeeds extends Model
{
    use HasFactory;

    /**
     * {@inheritdoc}
     *
     * Override default behaviour when a new tuple is added
     */
    protected static function boot()
    {
        parent::boot();
        static::created(fn($model) => RemoteFeedEvent::dispatchIf(
            $model->is_active,
            $model::withoutRelations()
        ));
        // requires thumbnails as should be removed from Redis/Filesystem
        static::deleting(fn($model) => RemoteFeedDeleting::dispatch($model::withoutRelations('pornstars')));
    }

    public $fillable = [
        'source',
        'processes_handler',
        'download_handler',
        'examine_handler',
    ];

    public function pornstars(): HasMany
    {
        return $this->hasMany(Pornstars::class, 'remote_feed_id');
    }

    public function thumbnails(): HasMany
    {
        return $this->hasMany(Thumbnails::class, 'remote_feed_id');
    }
}
