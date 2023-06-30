<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Thumbnails extends Model
{
    use HasFactory;

    public $fillable = [
        'url',
        'width',
        'height',
        'media',
    ];

    public function downloaded(): BelongsTo
    {
        return $this->belongsTo(DownloadedFiles::class, 'downloaded_file_id');
    }

    public function remoteFeeds(): BelongsTo
    {
        return $this->belongsTo(RemoteFeeds::class, 'id', 'remote_feed_id');
    }

    public function pornstars(): BelongsToMany
    {
        return $this->belongsToMany(Pornstars::class);
    }
}
