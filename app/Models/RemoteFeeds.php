<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use App\Events\RemoteFeedCreated;
use App\Events\RemoteFeedDeleting;
use App\Jobs\Common\DownloadJob;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Collection;

class RemoteFeeds extends Model
{
    use HasFactory;

    /**
     * {@inheritdoc}
     */
    protected static function boot()
    {
        parent::boot();
        static::created(fn ($model) => RemoteFeedCreated::dispatchIf(
            $model->is_active,
            $model->withoutRelations()
        ));
        static::deleting(fn ($model) => RemoteFeedDeleting::dispatch(
            $model->withoutRelations('pornstars')
        ));
    }

    /**
     * {@inheritdoc}
     */
    public $fillable = [
        'name',
        'downloaded_file_id',
        'source',
        'handle',
    ];

    /**
     * {@inheritdoc}
     */
    protected $appends = [
        'chain',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'handle'        => AsArrayObject::class,
    ];

    /**
     * Many to One Relation
     *
     * Gets the relation from `downloaded_files` table.
     * Points to file stored within filesystem
     *
     * @access  public
     * @return  BelongsTo
     */
    public function downloaded(): BelongsTo
    {
        return $this->belongsTo(DownloadedFiles::class, 'downloaded_file_id');
    }

    /**
     * Many to Many Relation
     *
     * Gets the relation from `downloaded_files` table via `thumbnails`
     * Points to images and other files stored within filesystem
     *
     * @access  public
     * @return  HasManyThrough
     */
    public function downloaded_files(): HasManyThrough
    {
        return $this->hasManyThrough(DownloadedFiles::class, Thumbnails::class, 'remote_feed_id', 'id', 'thumbnail_id');
    }

    /**
     * One to Many Relation
     *
     * Gets the relation from `pornstars` table.
     *
     * @access  public
     * @return  HasMany
     */
    public function pornstars(): HasMany
    {
        return $this->hasMany(Pornstars::class, 'remote_feed_id');
    }

    /**
     * One to Many Relation
     *
     * Gets the relation from `thumbnails` table.
     *
     * @access  public
     * @return  HasMany
     */
    public function thumbnails(): HasMany
    {
        return $this->hasMany(Thumbnails::class, 'remote_feed_id');
    }

    /**
     * Custom Attribute
     *
     * Generates a chain of jobs from handle.
     * Only structures them for when and if those jobs are needed.
     *
     * @access  public
     * @return  Collection
     */
    public function getChainAttribute(): Collection
    {
        return collect($this->handle)
            ->map(fn ($attributes, $class) => \class_exists($class) ? match ($class) {
                DownloadJob::class => new $class($this, ...$attributes),
                default            => new $class(...$attributes),
            } : null)
            ->filter(fn ($node) => $node !== null);
    }
}
