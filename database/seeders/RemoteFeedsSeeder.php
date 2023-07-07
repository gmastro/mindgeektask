<?php

namespace Database\Seeders;

use App\Jobs\Pornhub\ProcessJob;
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
                'downloaded_file_id'=> null,
                'source'            => 'https://www.pornhub.com/files/json_feed_pornstars.json',
                'is_active'         => true,
                'handle'            => ProcessJob::class,
                'created_at'        => Carbon::now(),
            ]
        ], [
            'source'
        ], [
            'downloaded_file_id', 'source', 'is_active', 'handle', 'created_at', 'updated_at'
        ]);
    }
}
