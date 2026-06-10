<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('duration_label')->nullable()->after('due_date');

            $table->decimal('amount_ht', 10, 2)->nullable()->after('duration_label');
            $table->decimal('tax_rate', 5, 2)->default(20)->after('amount_ht');
            $table->decimal('tax_amount', 10, 2)->nullable()->after('tax_rate');
            $table->decimal('amount_ttc', 10, 2)->nullable()->after('tax_amount');

            $table->string('cheque_number')->nullable()->after('reference');
            $table->string('cheque_bank')->nullable()->after('cheque_number');
            $table->date('cheque_date')->nullable()->after('cheque_bank');

            $table->string('bank_transfer_reference')->nullable()->after('cheque_date');
            $table->string('bank_name')->nullable()->after('bank_transfer_reference');

            $table->string('tpe_transaction_reference')->nullable()->after('bank_name');

            $table->string('receipt_number')->nullable()->after('tpe_transaction_reference');
            $table->string('receipt_file')->nullable()->after('receipt_number');

            $table->string('invoice_number')->nullable()->after('receipt_file');
            $table->string('invoice_file')->nullable()->after('invoice_number');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'duration_label',
                'amount_ht',
                'tax_rate',
                'tax_amount',
                'amount_ttc',
                'cheque_number',
                'cheque_bank',
                'cheque_date',
                'bank_transfer_reference',
                'bank_name',
                'tpe_transaction_reference',
                'receipt_number',
                'receipt_file',
                'invoice_number',
                'invoice_file',
            ]);
        });
    }
};