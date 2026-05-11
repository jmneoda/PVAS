<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_status_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('appointment_id');
            $table->string('status');                           // scheduled, confirmed, completed, no_show, cancelled
            $table->unsignedBigInteger('changed_by')->nullable(); // user who changed it
            $table->timestamp('changed_at')->useCurrent();

            $table->foreign('appointment_id')
                  ->references('id')->on('appointments')
                  ->onDelete('cascade');

            $table->foreign('changed_by')
                  ->references('id')->on('users')
                  ->onDelete('set null');

            $table->index(['appointment_id', 'changed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_status_histories');
    }
};