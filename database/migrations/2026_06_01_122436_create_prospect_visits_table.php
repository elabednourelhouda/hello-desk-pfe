<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prospect_visits', function (Blueprint $table) {
            $table->id();

            $table->foreignId('prospect_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('commercial_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('campus_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('space_type_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->date('visit_date');
            $table->time('visit_time')->nullable();

            $table->string('status')->default('planned');
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prospect_visits');
    }
};