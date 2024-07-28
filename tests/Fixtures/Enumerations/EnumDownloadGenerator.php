<?php

declare(strict_types=1);

enum EnumDownloadGenerator: int
{
    /**
     * Examine Case
     *
     * It will check if the content exists, also will bring any other information related to it.
     * Checks via provided uri if and when the content exists. I.e. if the url is valid and so on.
     */
    case Examine = (1 << 0);

    /**
     * Download Case
     *
     * It will download the content and store it to the filesystem
     */
    case Download = (1 << 1);

    /**
     * Filesystem Case
     *
     * It will store the content into the filesystem/alternative filesystem
     */
    case Filesystem = (1 << 2);

    /**
     * Database Case
     *
     * It will add all the content information into the database
     */
    case Database = (1 << 3);

    /**
     * Cache Case
     * 
     * It will attempt to cache the content into Redis.
     */
    case Cache = (1 << 4);
}