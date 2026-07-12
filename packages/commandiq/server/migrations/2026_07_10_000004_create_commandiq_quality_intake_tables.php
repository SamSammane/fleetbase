<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * QC review and service-intake tables — Sections 8.8 (FR-27..30, FR-58)
 * and 8.15 (FR-56).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('commandiq_qc_reviews', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uuid', 191)->nullable()->index();
            $table->string('public_id', 191)->nullable()->unique();
            $table->string('company_uuid', 191)->nullable()->index();
            $table->string('work_order_uuid', 191)->nullable()->index();
            $table->string('reviewer_uuid', 191)->nullable()->index();
            $table->string('technician_uuid', 191)->nullable()->index();
            $table->string('status')->nullable()->index();
            $table->json('checklist_results')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->json('photo_evidence')->nullable();
            $table->datetime('reviewed_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('commandiq_intake_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uuid', 191)->nullable()->index();
            $table->string('public_id', 191)->nullable()->unique();
            $table->string('company_uuid', 191)->nullable()->index();
            $table->string('subject')->nullable();
            $table->text('description')->nullable();
            $table->string('vin')->nullable()->index();
            $table->string('dsp_code')->nullable()->index();
            $table->string('station_code')->nullable()->index();
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('fault_type')->nullable()->index();
            $table->string('status')->nullable()->index();
            $table->string('work_order_uuid', 191)->nullable()->index();
            $table->datetime('submitted_at')->nullable();
            $table->datetime('triaged_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commandiq_intake_requests');
        Schema::dropIfExists('commandiq_qc_reviews');
    }
};
