<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PornstarsThumbnails extends Pivot
{
    public function pornstars(): BelongsToMany
    {
        return $this->belongsToMany(Pornstars::class);
    }

    public function thumbnails(): BelongsToMany
    {
        return $this->belongsToMany(Thumbnails::class);
    }
}
