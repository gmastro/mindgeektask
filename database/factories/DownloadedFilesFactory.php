<?php

namespace Database\Factories;

use App\Models\DownloadedFiles;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DownloadedFiles>
 */
class DownloadedFilesFactory extends Factory
{
    protected $model = DownloadedFiles::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $config   = config('filesystems.disks');
        $filename = \implode(".", [fake()->word(), fake()->fileExtension()]);
        $disk     = fake()->randomElement(\array_keys($config));
        $hash     = \md5(\implode("/", [$config[$disk]['root'], $filename]));
        return [
            'filesize'      => fake()->numberBetween(),
            'filename'      => $filename,
            'disk'          => $disk,
            'mime_type'     => fake()->mimeType(),
            'md5_hash'      => $hash,
            'is_cached'     => fake()->boolean(),
            'created_at'    => Carbon::now(),
            'updated_at'    => Carbon::now(),
        ];
    }
}
