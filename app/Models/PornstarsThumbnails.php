<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class PornstarsThumbnails extends Pivot
{
    public $increments = false;

    protected $table = 'pornstars_thumbnails';
}
