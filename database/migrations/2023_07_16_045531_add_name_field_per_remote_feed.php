<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('remote_feeds', function(BluePrint $table) {
            $table
                ->string('name', 128)
                ->comment('Label, name per source/feed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('remote_feeds', function(BluePrint $table) {
            $table
                ->dropColumn('name');
        });
    }
};
