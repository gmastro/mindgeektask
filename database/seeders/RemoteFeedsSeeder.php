<?php

namespace Database\Seeders;

use App\Customizations\Composites\PornstarsComponent;
use App\Customizations\Factories\CurlDownload;
use App\Customizations\Factories\CurlExaminer;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RemoteFeedsSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('remote_feeds')->upsert([
            [
                'source'            => 'https://www.pornhub.com/files/json_feed_pornstars.json',
                'is_active'         => true,
                'process_handler'   => PornstarsComponent::class,
                'download_handler'  => CurlDownload::class,
                'examine_handler'   => CurlExaminer::class,
                'created_at'        => Carbon::now(),
                'updated_at'        => Carbon::now(),
            ]
        ], [
            'source'
        ], [
            'source', 'is_active', 'process_handler', 'download_handler', 'examine_handler', 'created_at', 'updated_at'
        ]);
    }
}
