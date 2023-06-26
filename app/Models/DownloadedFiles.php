<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DownloadedFiles extends Model
{
    use HasFactory;

    protected static function bool()
    {
        parent::boot();
        // static::created(fn(DownloadedFiles $model) => DownloadedFilesCreatedEvent::dispatchIf(
        //     $model->is_cached,
        //     $model->withoutRelations()
        // ));
        // static::updated(fn(DownloadedFiles $model) => DownloadedFilesUpdatedEvent::dispatchIf(
        //     $model->wasChanged(['is_cached', 'filesize', 'deleted_at']),
        //     $model->withoutRelations()
        // ));
    }

    public $fillable = [
        'filesize',
        'filename',
        'disk',
        'mime_type',
        'is_cached',
    ];

    public function remote_feeds(): HasMany
    {
        return $this->hasMany('remote_feeds', 'downloaded_file_id');
    }

    public function thumbnails(): HasMany
    {
        return $this->hasMany('thumbnails', 'downloaded_file_id');
    }
}
