<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('legal_file_status')->default('incomplete');
            $table->timestamp('legal_file_completed_at')->nullable();
            $table->text('legal_file_notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'legal_file_status',
                'legal_file_completed_at',
                'legal_file_notes',
            ]);
        });
    }
};