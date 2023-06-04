<?php
declare(strict_types=1);

namespace App\Jobs;

use App\Customizations\Composites\ExamineComponent;
use App\Customizations\Factories\CurlExaminer;
use App\Events\RemoteFeedEvent;
use App\Models\RemoteFeeds;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessRemoteFeeds implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function uniqueId(): string
    {
        return __CLASS__;
    }

    private $female = [
        "attributes"            => [
            "hairColor"             => "Blonde",
            "ethnicity"             => "White",
            "tattoos"               => true,
            "piercings"             => false,
            "gender"                => "female",
            "orientation"           => "straight",
            "stats"                 => [
                "subscriptions"         => 652,
                "monthlySearches"       => 177900,
                "views"                 => 166692,
                "videosCount"           => 5,
                "premiumVideosCount"    => 4,
                "whiteLabelVideoCount"  => 4,
                "rank"                  => 7950,
                "rankPremium"           => 8216,
                "rankWl"                => 7650
            ],
        ],
        "id"                    => 79,
        "name"                  => "alex love",
        "license"               => "REGULAR",
        "wlStatus"              => "1",
        "aliases"               => [],
        "link"                  => "https://www.pornhub.com/pornstar/alex-love",
        "thumbnails"            => [
            [
                "height"            => 344,
                "width"             => 234,
                "type"              => "pc",
                "urls"              => [
                    "https://di.phncdn.com/pics/pornstars/000/000/079/(m=lciuhScOb_c)(mh=nQgXwqViq9_H5KzB)thumb_1152691.jpg"
                ]
            ], [
                "height"            => 344,
                "width"             => 234,
                "type"              => "mobile",
                "urls"              => [
                    "https://di.phncdn.com/pics/pornstars/000/000/079/(m=lciuhScOb_c)(mh=nQgXwqViq9_H5KzB)thumb_1152691.jpg"
                ]
            ], [
                "height"            => 344,
                "width"             => 234,
                "type"              => "tablet",
                "urls"              => [
                    "https://di.phncdn.com/pics/pornstars/000/000/079/(m=lciuhScOb_c)(mh=nQgXwqViq9_H5KzB)thumb_1152691.jpg"
                ]
            ]
        ],
    ];

    private $male = [
        "attributes"        => [
            "hairColor"             => "Brunette",
            "ethnicity"             => "White",
            "tattoos"               => false,
            "piercings"             => false,
            "gender"                => "male",
            "orientation"           => "straight",
            "age"                   => 57,
            "stats"                 => [
                "subscriptions"         => 2002,
                "monthlySearches"       => 282400,
                "views"                 => 426328,
                "videosCount"           => 178,
                "premiumVideosCount"    => 22,
                "whiteLabelVideoCount"  => 162,
                "rank"                  => 6661,
                "rankPremium"           => 6860,
                "rankWl"                => 6412
            ]
        ],
        "id"                => 80,
        "name"              => "Alex Sanders",
        "license"           => "REGULAR",
        "wlStatus"          => "1",
        "aliases"           => [
            "scott boisvert",
            "alex sander"
        ],
        "link"              => "https://www.pornhub.com/pornstar/alex-sanders",
        "thumbnails"        => [
            [
                "height"        => 344,
                "width"         => 234,
                "type"          => "pc",
                "urls"          => [
                    "https://di.phncdn.com/pics/pornstars/000/000/080/(m=lciuhScOb_c)(mh=OTBVd7IP3Org5gwU)thumb_466882.jpg"
                ]
            ], [
                "height"        => 344,
                "width"         => 234,
                "type"          => "mobile",
                "urls"          => [
                    "https://di.phncdn.com/pics/pornstars/000/000/080/(m=lciuhScOb_c)(mh=OTBVd7IP3Org5gwU)thumb_466882.jpg"
                ]
            ], [
                "height"        => 344,
                "width"         => 234,
                "type"          => "tablet",
                "urls"          => [
                    "https://di.phncdn.com/pics/pornstars/000/000/080/(m=lciuhScOb_c)(mh=OTBVd7IP3Org5gwU)thumb_466882.jpg"
                ]
            ]
        ]
    ];

    /**
     * Execute the job.
     *
     * The following steps should happen in the following order
     * - Get all those sources defined within the database (in our case just one)
     * - Download all those sources(assuming that the link is correct) , which, are recent compared to source latest
     *   update and place them into Storage::disk('downloads').
     * - Downloaded filename will be an md5 hash of given source/url
     * - Increment tuple counter via update
     * - Each updated tuple will trigger an event
     * - The event will use tuple's handle field to call that class which will restructure data received from the file.
     * - The callable will perform the following tasks;
     *     - mapping (set items based on given source id's or unique identifiers)
     *     - remove data, files and cache from missing identifiers compared to the set of existing records
     *     - insert data 
     * - This will trigger a new event, which, will restructure the content within the items based on predefined
     * - Each updated tuple contains a handle (a class name instance) 
     * 
     * Examine
     */
    public function handle(): void
    {
        RemoteFeeds::all()
            ->reject(function ($feed): bool {
                return $feed->is_active
                    && (new ExamineComponent($feed))->execute();
                // $rf->updated_at->gt((new CurlExaminer($rf->source))->getLastModified());
            })
            ->map(function ($rf): void {
                RemoteFeedEvent::dispatch($rf::withoutRelations(), true, false, false);
            });
    }
}
