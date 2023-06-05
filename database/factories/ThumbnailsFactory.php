<?php

namespace Database\Factories;

use App\Models\RemoteFeeds;
use App\Models\Thumbnails;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Thumbnails>
 */
class ThumbnailsFactory extends Factory
{
    protected $model = Thumbnails::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'remote_feed_id'    => RemoteFeeds::factory(),
            'url'               => fake()->unique()->url(),
            'width'             => fake()->randomNumber(3),
            'height'            => fake()->randomNumber(3),
            'media'             => \implode(',', fake()->randomElements(['pc','tablet','mobile'])),
            'created_at'        => Carbon::now(),
            'updated_at'        => Carbon::now(),
        ];
    }
}
