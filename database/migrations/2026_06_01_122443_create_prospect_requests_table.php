<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prospect_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('prospect_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->date('request_date');

            $table->string('request_type')->nullable();

            $table->foreignId('campus_id')
                ->nullable()
                ->constrained('campuses')
                ->nullOnDelete();

            $table->foreignId('space_type_id')
                ->nullable()
                ->constrained('space_types')
                ->nullOnDelete();

            $table->date('desired_start_date')->nullable();

            $table->string('duration_type')->nullable();

            $table->decimal('budget', 10, 2)->nullable();

            $table->string('status')->default('new');

            $table->text('description');
            $table->text('response_notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prospect_requests');
    }
};