<?php

namespace Database\Factories;

use App\Models\Pornstars;
use App\Models\PornstarsThumbnails;
use App\Models\Thumbnails;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PornstarsThumbnails>
 */
class PornstarsThumbnailsFactory extends Factory
{
    protected $model = PornstarsThumbnails::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pornstar_id'   => Pornstars::factory(),
            'thumbnail_id'  => Thumbnails::factory(),
            'created_at'    => Carbon::now(),
            'updated_at'    => Carbon::now(),
        ];
    }
}
