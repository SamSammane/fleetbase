<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Campaign management tables — Section 8.13 (FR-43..45).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('commandiq_campaigns', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uuid', 191)->nullable()->index();
            $table->string('public_id', 191)->nullable()->unique();
            $table->string('company_uuid', 191)->nullable()->index();
            $table->string('code')->nullable()->index();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->string('type')->nullable()->index();
            $table->string('status')->nullable()->index();
            $table->string('priority')->nullable();
            $table->string('work_order_category')->nullable();
            $table->datetime('starts_at')->nullable();
            $table->datetime('ends_at')->nullable();
            $table->boolean('bundling_enabled')->default(true);
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('commandiq_campaign_assignments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uuid', 191)->nullable()->index();
            $table->string('public_id', 191)->nullable()->unique();
            $table->string('company_uuid', 191)->nullable()->index();
            $table->string('campaign_uuid', 191)->nullable()->index();
            $table->string('subject_type')->nullable();
            $table->string('subject_uuid', 191)->nullable()->index();
            $table->string('vin')->nullable()->index();
            $table->string('status')->nullable()->index();
            $table->string('work_order_uuid', 191)->nullable()->index();
            $table->datetime('scheduled_at')->nullable();
            $table->datetime('completed_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commandiq_campaign_assignments');
        Schema::dropIfExists('commandiq_campaigns');
    }
};
