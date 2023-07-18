<?php

namespace Database\Factories;

use App\Models\DownloadedFiles;
use App\Models\RemoteFeeds;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RemoteFeeds>
 */
class RemoteFeedsFactory extends Factory
{
    protected $model = RemoteFeeds::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'              => fake()->name(),
            'downloaded_file_id'=> DownloadedFiles::factory(),
            'source'            => fake()->unique()->url(),
            'is_active'         => fake()->boolean(),
            'examine_counter'   => fake()->randomNumber(5),
            'download_counter'  => fake()->randomNumber(3),
            'handle'            => '[]',
            'created_at'        => Carbon::now(),
            'updated_at'        => Carbon::now(),
        ];
    }
}
