<?php

declare(strict_types=1);

namespace App\Models;

use App\Events\RemoteFeedDeleting;
use App\Events\RemoteFeedEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class RemoteFeeds extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();
        static::created(fn ($model) => RemoteFeedEvent::dispatchIf(
            $model->is_active,
            $model->withoutRelations()
        ));
        static::deleting(fn ($model) => RemoteFeedDeleting::dispatch(
            $model::withoutRelations('pornstars')
        ));
    }

    public $fillable = [
        'downloaded_file_id',
        'source',
        'handle',
    ];

    public function downloaded(): BelongsTo
    {
        return $this->belongsTo(DownloadedFiles::class, 'downloaded_file_id');
    }

    public function downloaded_files(): HasManyThrough
    {
        return $this->hasManyThrough(DownloadedFiles::class, Thumbnails::class, 'remote_feed_id', 'id', 'thumbnail_id');
    }

    public function pornstars(): HasMany
    {
        return $this->hasMany(Pornstars::class, 'remote_feed_id');
    }

    public function thumbnails(): HasMany
    {
        return $this->hasMany(Thumbnails::class, 'remote_feed_id');
    }
}
