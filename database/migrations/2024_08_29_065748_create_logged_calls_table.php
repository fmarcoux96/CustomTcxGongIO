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
        Schema::create('logged_calls', function (Blueprint $table) {
            $table->id();
            $table->dateTime('call_start')->nullable();
            $table->dateTime('call_end')->nullable();
            $table->string('call_status')->nullable();
            $table->string('call_direction')->nullable();
            $table->string('call_type')->nullable();
            $table->string('call_duration')->nullable();
            $table->string('call_text')->nullable();
            $table->string('caller_name')->nullable();
            $table->string('caller_number')->nullable();
            $table->string('agent_extension')->nullable();
            $table->string('agent_name')->nullable();
            $table->string('agent_email')->nullable();
            $table->string('queue_extension')->nullable();
            $table->integer('tcx_call_id')->nullable();
            $table->integer('tcx_recording_id')->nullable();
            $table->string('tcx_recording_filename')->nullable();
            $table->string('zoho_call_id')->nullable();
            $table->string('gong_call_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logged_calls');
    }
};
