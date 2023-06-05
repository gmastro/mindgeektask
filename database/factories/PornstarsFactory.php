<?php

namespace Database\Factories;

use App\Models\Pornstars;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pornstars>
 */
class PornstarsFactory extends Factory
{
    protected $model = Pornstars::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $attributes = \array_intersect_key([
            "hairColor"     => \implode("|", fake()->randomElements([
                "Auburn", "Blonde", "Bald", "Black", "Brunnete", "Ginger", "Pink", "Red", "Other"
            ])),
            "ethnicity"     => \implode("|", fake()->randomElements([
                "Asian", "Black", "Indian", "Latin", "Middle Eastern", "Other", "White"
            ])),
            "breast"        => null,
            "age"           => fake()->numberBetween(18, 60),
        ], \array_keys(fake()->randomElements([
            'hairColor',
            'ethnicity',
            'age',
            'breast',
        ]))) + [
            "tattoos"       => fake()->boolean(),
            "piercings"     => fake()->boolean(),
            "gender"        => fake()->randomElement([
                "Couple",
                "Female",
                "f2m",
                "Male",
                "m2f",
                "Non-Binary",
                "Other",
                "Same Sex Couple (Female)",
                "Same Sex Couple (Male)",
                "uncategorized"
            ]),
            "orientation"   => fake()->randomElement(["straight","gay"])
        ];

        if (isset($attributes['breast'])) {
            unset($attributes['breast']);
            $attributes['breastSize'] = fake()->numberBetween(25, 80);
            $attributes['breastType'] = \str_repeat(fake()->regexify('[A-P]{1}'), fake()->numberBetween(1, 3));
        }

        return [
            'id'            => fake()->unique()->randomNumber(9),
            'name'          => fake()->name(),
            'link'          => fake()->unique()->url(),
            'license'       => fake()->randomElement(['REGULAR', 'PREMIUM']),
            'wlStatus'      => fake()->boolean(),
            'attributes'    => $attributes,
            'stats'         => [
                "subscriptions"         => fake()->randomNumber(5),
                "monthlySearches"       => fake()->randomNumber(9),
                "views"                 => fake()->randomNumber(9),
                "videosCount"           => fake()->randomNumber(5),
                "premiumVideosCount"    => fake()->randomNumber(5),
                "whiteLabelVideoCount"  => fake()->randomNumber(5),
                "rank"                  => fake()->randomNumber(5),
                "rankPremium"           => fake()->randomNumber(5),
                "rankWl"                => fake()->randomNumber(5),
            ],
            'aliases'       => fake()->words(),
        ];
    }
}
