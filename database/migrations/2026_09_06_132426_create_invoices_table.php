<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_id')->nullable()->index();

            // Link to shopmade-jobs when it is installed (its jobs table, not Laravel's queue table).
            if (Schema::hasTable('jobs') && Schema::hasColumn('jobs', 'customer_id')) {
                $table->foreign('job_id')->references('id')->on('jobs')->nullOnDelete();
            }
            $table->foreignId('quote_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('status');
            $table->decimal('total', 10, 2)->default(0);
            $table->date('issued_at')->nullable();
            $table->date('due_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
