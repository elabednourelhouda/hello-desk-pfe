<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spaces', function (Blueprint $table) {
            $table->id();

            $table->foreignId('campus_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('floor_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('space_type_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('name');
            $table->string('code')->unique();

            $table->integer('capacity')->default(1);
            $table->decimal('surface', 8, 2)->nullable();

            $table->decimal('price_per_hour', 10, 2)->nullable();
            $table->decimal('price_per_day', 10, 2)->nullable();
            $table->decimal('price_per_month', 10, 2)->nullable();

            $table->string('status')->default('Disponible');

            $table->text('description')->nullable();
            $table->text('notes')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['campus_id', 'floor_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spaces');
    }
};