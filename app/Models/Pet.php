<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pet extends Model
{
    protected $table = 'pets';

    protected $fillable = [
        'customer_id',
        'pet_name',
        'species',
        'breed',
        'gender',
        'birthdate',
        'color',
        'weight',
        'medical_notes',
    ];

    /* ── Relationships ── */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'pet_id');
    }

    public function vaccinations()
    {
        return $this->hasMany(Vaccination::class, 'pet_id');
    }

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class, 'pet_id');
    }
}