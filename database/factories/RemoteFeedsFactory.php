<?php

namespace Database\Factories;

use App\Customizations\Composites\PornstarsComponent;
use App\Customizations\Factories\CurlDownload;
use App\Customizations\Factories\CurlExaminer;
use App\Customizations\Factories\FileGetContentsDownload;
use App\Customizations\Factories\GetHeadersExaminer;
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
            'downloaded_file_id'=> null,
            'source'            => fake()->unique()->url(),
            'is_active'         => fake()->boolean(),
            'examine_counter'   => fake()->randomNumber(5),
            'download_counter'  => fake()->randomNumber(3),
            'process_handler'   => fake()->randomElement([PornstarsComponent::class, Carbon::class]),
            'examine_handler'   => fake()->randomElement([CurlExaminer::class, GetHeadersExaminer::class]),
            'download_handler'  => fake()->randomElement([CurlDownload::class, FileGetContentsDownload::class]),
            'created_at'        => Carbon::now(),
            'updated_at'        => Carbon::now(),
        ];
    }
}
