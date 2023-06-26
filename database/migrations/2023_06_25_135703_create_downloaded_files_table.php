<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function tableDefault(Blueprint $table): Blueprint
    {
        $table->engine = 'InnoDB';
        $table->charset = 'utf8mb4';
        $table->collation = 'utf8mb4_unicode_ci';
        return $table;
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('downloaded_files', function(Blueprint $table) {
            $table = $this->tableDefault($table);
            $table
                ->id();
            $table
                ->unsignedBigInteger('filesize')
                ->default(0)
                ->comment('will allow to trigger updates, when and if a filesize changes');
            $table
                ->string('filename', 256);
            $table
                ->string('disk', 32)
                ->default('downloads')
                ->comment('Storage selected medium, see config/filesystems.php');
            $table
                ->string('mime_type', 128)
                ->comment('Cache in redis, or expand for different caching strategies');
            $table
                ->string('md5_hash', 32)
                ->comment('stores full disk path and filename as an md5 hash');
            $table
                ->boolean('is_cached')
                ->default(true)
                ->comment('Cache in redis, or expand for different caching strategies');
            $table
                ->unique(['filename', 'disk'], 'unq_downloaded_files_fd');
            $table
                ->index(['md5_hash', 'is_cached'], 'idx_downloaded_files_mhic');
            $table
                ->timestamps();
            $table
                ->softDeletes();
        });

        Schema::table('remote_feeds', function(BluePrint $table) {
            $table
                ->foreignId('downloaded_file_id')
                ->nullable()
                ->comment('surrogate/foreign key')
                ->constrained('downloaded_files', 'id', 'fk_remote_feeds_downloaded_files_iddf')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });

        Schema::table('thumbnails', function(Blueprint $table) {
            $table
                ->foreignId('downloaded_file_id')
                ->nullable()
                ->comment('surrogate/foreign key')
                ->constrained('downloaded_files', 'id', 'fk_thumbnails_downloaded_files_iddf')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('remote_feeds', function(Blueprint $table) {
            $table->dropForeign('fk_remote_feeds_downloaded_files_iddf');
            $table->dropColumn('downloaded_file_id');
        });

        Schema::table('thumbnails', function(Blueprint $table) {
            $table->dropForeign('fk_thumbnails_downloaded_files_iddf');
            $table->dropColumn('downloaded_file_id');
        });

        Schema::dropIfExists('downloaded_files');
    }
};
