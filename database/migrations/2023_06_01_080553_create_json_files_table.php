<?php

use Illuminate\Database\Migrations\Migration;
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
        Schema::create('remote_feeds', function (Blueprint $table) {
            $table = $this->tableDefault($table);
            $table
                ->id();
            $table
                ->string('source', 512)
                ->unique('unq_remote_feeds_s')
                ->comment('source to examine and download content from');
            $table
                ->boolean('is_active')
                ->default(1)
                ->comment('deactivate feed for invalid links');
            $table
                ->integer('download_counter')
                ->default(0)
                ->nullable()
                ->comment('times dowloaded content');
            $table
                ->integer('examine_counter')
                ->default(0)
                ->nullable()
                ->comment('times invoked an examiner class');
            $table
                ->string('process_handler', 255)
                ->comment('class to process content');
            $table
                ->string('download_handler', 255)
                ->comment('download class name to prefer, using curl or file_get_contents');
            $table
                ->string('examine_handler', 255)
                ->comment('examiner class name to prefer, using curl or get_headers');
            $table->timestamps();
        });

        Schema::create('pornstars', function (Blueprint $table) {
            $table = $this->tableDefault($table);
            $table
                ->unsignedBigInteger('id');
            $table
                ->foreignId('remote_feed_id')
                ->constrained('remote_feeds', 'id', 'fk_pornstars_remote_feeds')
                ->cascadeOnDelete();
            $table
                ->string('name', 128);
            $table
                ->string('link', 512);
            $table
                ->string('license', 32);
            $table
                ->boolean('wlStatus');
            $table
                ->json('attributes');
            $table
                ->json('stats');
            $table
                ->json('aliases');
            $table
                ->timestamps();
            $table
                ->primary('id');
        });

        Schema::create('thumbnails', function (Blueprint $table) {
            $table = $this->tableDefault($table);
            $table
                ->id();
            $table
                ->foreignId('remote_feed_id')
                ->constrained('remote_feeds', 'id', 'fk_thumbnails_remote_feeds')
                ->cascadeOnDelete();
            $table
                ->string('url');
            $table
                ->integer('width')
                ->default(234);
            $table
                ->integer('height')
                ->default(344);
            $table
                ->set('media', ['pc','mobile','tablet'])
                ->default('pc');
            $table
                ->timestamps();
            $table
                ->unique(['url', 'media'], 'unq_thumbnails_um');
        });

        Schema::create('pornstars_thumbnails', function(Blueprint $table) {
            $table = $this->tableDefault($table);
            $table
                ->foreignId('pornstar_id')
                ->constrained('pornstars', 'id', 'fk_pornstars_thumbnails_pornstars')
                ->cascadeOnDelete();
            $table
                ->foreignId('thumbnail_id')
                ->constrained('thumbnails', 'id', 'fk_pornstars_thumbnails_thumbnails')
                ->cascadeOnDelete();
            $table->
                timestamps();
            $table
                ->primary(['pornstar_id', 'thumbnail_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('remote_feeds');
        Schema::dropIfExists('pornstars');
        Schema::dropIfExists('thumbnails');
        Schema::dropIfExists('pornstars_thumbnails');
        Schema::enableForeignKeyConstraints();
    }
};
