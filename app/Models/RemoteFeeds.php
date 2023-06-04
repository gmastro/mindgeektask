<?php

namespace App\Models;

use App\Events\RemoteFeedCreated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RemoteFeeds extends Model
{
    use HasFactory;

    public $fillable = [
        'source',
        'processes_handler',
        'download_handler',
        'examine_handler',
    ];

    protected $dispatchesEvents = [
         'deleted'   => RemoteFeedDeleted::class,
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
