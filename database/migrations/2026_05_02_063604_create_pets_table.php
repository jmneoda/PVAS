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
        Schema::create('pets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnDelete();

            $table->string('pet_name');

            $table->enum('species', [
                'Dog',
                'Cat',
                'Bird',
                'Rabbit',
                'Hamster',
                'Other'
            ]);

            $table->string('breed')->nullable();

            $table->enum('gender', [
                'Male',
                'Female'
            ])->nullable();

            $table->date('birthdate')->nullable();

            $table->string('color')->nullable();

            $table->decimal('weight', 5, 2)->nullable();

            $table->text('medical_notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pets');
    }
};