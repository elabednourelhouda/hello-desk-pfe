<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->unsignedInteger('grid_column')->nullable();
            $table->unsignedInteger('grid_row')->nullable();
            $table->unsignedInteger('grid_width')->nullable();
            $table->unsignedInteger('grid_height')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->dropColumn(['grid_column', 'grid_row', 'grid_width', 'grid_height']);
        });
    }
};
