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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnDelete();

            $table->foreignId('pet_id')
                ->constrained('pets')
                ->cascadeOnDelete();

            $table->foreignId('veterinarian_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->date('scheduled_date');

            $table->time('scheduled_time');

            $table->text('reason_for_visit')->nullable();

            /*
             | Status lifecycle (admin-managed):
             |   scheduled  → appointment has been booked, awaiting visit
             |   confirmed  → appointment details verified / reminder sent
             |   completed  → visit finished successfully
             |   no_show    → customer did not arrive
             |   cancelled  → appointment was cancelled by either party
             |
             | Admins update status manually as needed.
             | "pending" / "processing" have been removed.
             */
            $table->enum('status', [
                'scheduled',
                'confirmed',
                'completed',
                'no_show',
                'canceled',
            ])->default('scheduled');


            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')           // track who last changed the status
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};