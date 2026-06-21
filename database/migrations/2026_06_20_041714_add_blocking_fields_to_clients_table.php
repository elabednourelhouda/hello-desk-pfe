<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('clients', 'status')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->string('status')->default('active');
            });
        }

        Schema::table('clients', function (Blueprint $table) {
            if (! Schema::hasColumn('clients', 'blocked_at')) {
                $table->timestamp('blocked_at')->nullable();
            }

            if (! Schema::hasColumn('clients', 'blocked_by')) {
                $table->foreignId('blocked_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('clients', 'block_reason')) {
                $table->text('block_reason')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (Schema::hasColumn('clients', 'blocked_by')) {
                $table->dropConstrainedForeignId('blocked_by');
            }

            if (Schema::hasColumn('clients', 'blocked_at')) {
                $table->dropColumn('blocked_at');
            }

            if (Schema::hasColumn('clients', 'block_reason')) {
                $table->dropColumn('block_reason');
            }
        });
    }
};