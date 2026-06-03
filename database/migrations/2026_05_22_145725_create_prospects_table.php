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
        Schema::create('prospects', function (Blueprint $table) {
            $table->id();

            $table->string('full_name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('company_name')->nullable();

            $table->text('need')->nullable();

            $table->foreignId('preferred_campus_id')
                ->nullable()
                ->constrained('campuses')
                ->nullOnDelete();

            $table->foreignId('preferred_space_type_id')
                ->nullable()
                ->constrained('space_types')
                ->nullOnDelete();

            $table->decimal('budget', 10, 2)->nullable();

            $table->string('source')->nullable();

            $table->string('crm_status')->default('new');

            $table->text('notes')->nullable();

            $table->foreignId('assigned_to')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('converted_client_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('converted_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prospects');
    }
};
