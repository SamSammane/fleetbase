<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Warranty claims and RMA tables — Sections 8.10 (FR-36/37) and 8.12 (FR-41/42).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('commandiq_warranty_claims', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uuid', 191)->nullable()->index();
            $table->string('public_id', 191)->nullable()->unique();
            $table->string('company_uuid', 191)->nullable()->index();
            $table->string('warranty_uuid', 191)->nullable()->index();
            $table->string('work_order_uuid', 191)->nullable()->index();
            $table->string('claim_number')->nullable()->index();
            $table->string('status')->nullable()->index();
            $table->datetime('submitted_at')->nullable();
            $table->datetime('resolved_at')->nullable();
            $table->integer('claimed_amount')->nullable();
            $table->integer('recovered_amount')->nullable();
            $table->string('currency', 3)->nullable();
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('commandiq_rma_cases', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uuid', 191)->nullable()->index();
            $table->string('public_id', 191)->nullable()->unique();
            $table->string('company_uuid', 191)->nullable()->index();
            $table->string('rma_number')->nullable()->index();
            $table->string('device_serial')->nullable()->index();
            $table->string('vehicle_device_uuid', 191)->nullable()->index();
            $table->string('part_uuid', 191)->nullable()->index();
            $table->string('status')->nullable()->index();
            $table->string('disposition')->nullable()->index();
            $table->string('depot_place_uuid', 191)->nullable()->index();
            $table->datetime('shipped_at')->nullable();
            $table->datetime('received_at')->nullable();
            $table->datetime('closed_at')->nullable();
            $table->string('core_status')->nullable()->index();
            $table->integer('core_credit_amount')->nullable();
            $table->string('currency', 3)->nullable();
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commandiq_rma_cases');
        Schema::dropIfExists('commandiq_warranty_claims');
    }
};
