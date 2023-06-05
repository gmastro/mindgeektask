<?php

namespace Database\Seeders;

use App\Customizations\Composites\PornstarsComponent;
use App\Customizations\Factories\CurlDownload;
use App\Customizations\Factories\CurlExaminer;
use App\Models\RemoteFeeds;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PornstarsSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        RemoteFeeds::factory()->create([
            'source'            => 'https://www.pornhub.com/files/json_feed_pornstars.json',
            'is_active'         => true,
            'process_handler'   => PornstarsComponent::class,
            'download_handler'  => CurlDownload::class,
            'examine_handler'   => CurlExaminer::class,
        ]);
    }
}
