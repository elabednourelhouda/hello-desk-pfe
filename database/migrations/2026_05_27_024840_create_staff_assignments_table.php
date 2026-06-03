<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('commercial_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('campus_id')
                ->constrained('campuses')
                ->restrictOnDelete();

            $table->foreignId('floor_id')
                ->nullable()
                ->constrained('floors')
                ->restrictOnDelete();

            $table->foreignId('assigned_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['commercial_id', 'campus_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_assignments');
    }
};