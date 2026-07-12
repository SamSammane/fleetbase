<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Forecasting tables — Section 8.2 (FR-3..5, FR-19..22).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('commandiq_availability_windows', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uuid', 191)->nullable()->index();
            $table->string('public_id', 191)->nullable()->unique();
            $table->string('company_uuid', 191)->nullable()->index();
            $table->string('subject_type')->nullable();
            $table->string('subject_uuid', 191)->nullable()->index();
            $table->string('segment', 12)->nullable()->index();
            $table->string('place_uuid', 191)->nullable()->index();
            $table->string('location_code')->nullable()->index();
            $table->datetime('starts_at')->nullable()->index();
            $table->datetime('ends_at')->nullable();
            $table->float('confidence')->nullable();
            $table->string('source')->nullable();
            $table->string('status')->nullable()->index();
            $table->datetime('validated_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('commandiq_return_patterns', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uuid', 191)->nullable()->index();
            $table->string('public_id', 191)->nullable()->unique();
            $table->string('company_uuid', 191)->nullable()->index();
            $table->string('dsp_code')->nullable()->index();
            $table->string('station_code')->nullable()->index();
            $table->string('place_uuid', 191)->nullable()->index();
            $table->string('vehicle_uuid', 191)->nullable()->index();
            $table->unsignedTinyInteger('weekday')->nullable();
            $table->time('avg_return_time')->nullable();
            $table->float('stddev_minutes')->nullable();
            $table->unsignedInteger('sample_size')->nullable();
            $table->unsignedInteger('lookback_days')->nullable();
            $table->datetime('computed_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commandiq_availability_windows');
        Schema::dropIfExists('commandiq_return_patterns');
    }
};
