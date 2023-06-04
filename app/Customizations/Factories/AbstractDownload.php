<?php
declare(strict_types=1);

namespace App\Customizations\Factories;

use App\Customizations\Factories\InterfaceDownload;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

abstract class AbstractDownload implements InterfaceDownload
{
    /**
     * Source Property
     *
     * Link or url to retrieve content from
     *
     * @access  protected
     * @var     string $source
     */
    protected $source;

    /**
     * Path Property
     *
     * Generated md5 hash filename from source property
     *
     * @access  protected
     * @var     string $path
     */
    protected $path;

    /**
     * Contents Property
     *
     * Available contents raw format right after performed download
     * By default this is set to false to match {@see \file_get_contents}
     *
     * @access  protected
     * @var     mixed $contents
     */
    protected $contents = false;

    /**
     * Disk Property
     *
     * Sets an instance of selected {@see \Illuminate\Contracts\Filesystem\Filesystem}
     *
     * @access  protected
     * @var     \Illuminate\Contracts\Filesystem\Filesystem $disk
     */
    protected $disk;

    /**
     * Extension Property
     *
     * Stores the extension of the file right after it is downloaded and stored
     *
     * @access  protected
     * @var     string
     */
    protected $extension;

    /**
     * Magic Constructor
     *
     * Adds initial information for storing content
     *
     * @access  public
     * @param   string $source Url to get information from
     * @param   Illuminate\Contracts\Filesystem\Filesystem $disk **Default `null`**, set storage container
     */
    public function __construct(string $source, Filesystem $disk = null)
    {
        $this->setSource($source);
        $this->setPath(\md5($source));
        $this->disk = $disk ?? Storage::disk('downloads');
    }

    /**
     * Source Setter
     *
     * Add the link from which we will attempt to download content
     *
     * @access  public
     * @param   string $source Url to get information from
     * @return  void
     */
    public function setSource(string $source): void
    {
        $this->source = $source;
    }

    /**
     * Path Setter
     *
     * Add the location of the file located within the given {@see Illuminate\Support\Facades\Storage::disk}
     *
     * @access  public
     * @param   string $path Filename or relative path
     * @return  void
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents(): mixed
    {
        return $this->contents;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtension(): string
    {
        return $this->extension;
    }

    /**
     * {@inheritdoc}
     */
    public function exists(): bool
    {
        return $this->disk->exists($this->getPath());
    }

    /**
     * {@inheritdoc}
     */
    public function unlink(): bool
    {
        return $this->disk->delete($this->path);
    }

    /**
     * Extension Setter
     *
     * Provide or get the extension from the given file
     *
     * @access  protected
     * @return  void
     */
    protected function setExtension(string $extension = null): void
    {
        $this->extension = $extension ?? \pathinfo(storage_path($this->disk->path($this->path)), PATHINFO_EXTENSION);
    }
}
