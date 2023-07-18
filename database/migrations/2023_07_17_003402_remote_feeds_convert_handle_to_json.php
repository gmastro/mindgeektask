<?php

use App\Models\RemoteFeeds;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('remote_feeds', function (Blueprint $table) {
            $table
                ->json('handle')
                ->comment('list of jobs/classes')
                ->change();

            DB::transaction( fn() => RemoteFeeds::all()->map( function($model) {
                $model->handle = $model->handle !== null ? [$model->handle] : [];
                $model->save();
            }));
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('remote_feeds', function (Blueprint $table) {
            $table
                ->string('handle')
                ->comment('class to process content')
                ->change();
        });
    }
};
