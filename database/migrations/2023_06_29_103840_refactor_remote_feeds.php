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
                ->renameColumn('process_handler', 'handle');
            $table
                ->dropColumn(['download_handler', 'examine_handler']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('remote_feeds', function(BluePrint $table) {
            $table
                ->renameColumn('handle', 'process_handler');
            $table
                ->string('download_handler', 255)
                ->comment('download class name to prefer, using curl or file_get_contents');
            $table
                ->string('examine_handler', 255)
                ->comment('examiner class name to prefer, using curl or get_headers');
        });
    }
};
