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
                ->string('source')
                ->unique();
            $table
                ->integer('counter')
                ->default(0)
                ->nullable();
            $table->timestamps();
        });

        Schema::create('pornstars', function (Blueprint $table) {
            $table = $this->tableDefault($table);
            $table
                ->id();
            $table
                ->foreignId('remote_feed_id')
                ->constrained('remote_feeds', 'id', 'fk_pornstars_remote_feeds')
                ->cascadeOnDelete();
            $table
                ->string('name')
                ->unique();
            $table
                ->string('link');
            $table
                ->string('licence');
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
                ->integer('width');
            $table
                ->integer('height');
            $table
                ->set('media', ['pc','mobile','tablet']);
            $table
                ->timestamps();
        });

        Schema::create('pornstars_thumbnails', function(Blueprint $table) {
            $table = $this->tableDefault($table);
            $table
                ->foreignId('pornstar_id')
                ->constrained('pornstars', 'id', 'fk_pornstards_thumbnails_pornstars')
                ->cascadeOnDelete();
            $table
                ->foreignId('thumbnail_id')
                ->constrained('thumbnails', 'id', 'fk_pornstars_thumbnails_thumbnails')
                ->cascadeOnDelete();
            $table
                ->unique(['pornstar_id', 'thumbnail_id']);
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
