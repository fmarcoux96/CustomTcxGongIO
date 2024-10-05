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
        Schema::table('logged_calls', function (Blueprint $table) {
            $table->string('entity_id')->nullable();
            $table->string('entity_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logged_calls', function (Blueprint $table) {
            $table->dropColumn('entity_id');
            $table->dropColumn('entity_type');
        });
    }
};
