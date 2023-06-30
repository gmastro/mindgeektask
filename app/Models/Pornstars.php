<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    public function remoteFeeds(): BelongsTo
    {
        return $this->belongsTo(RemoteFeeds::class);
    }

    public function thumbnails(): BelongsToMany
    {
        return $this->belongsToMany(Thumbnails::class);
    }
}
