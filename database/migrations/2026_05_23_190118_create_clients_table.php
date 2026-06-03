<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('prospect_id')
                ->nullable()
                ->constrained('prospects')
                ->nullOnDelete();

            $table->string('full_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('company_name')->nullable();

            $table->foreignId('main_campus_id')
                ->nullable()
                ->constrained('campuses')
                ->nullOnDelete();

            $table->string('status')->default('active');

            $table->text('billing_info')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique('user_id');
            $table->unique('prospect_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};