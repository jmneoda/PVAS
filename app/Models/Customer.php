<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'customers';

    protected $fillable = [
        'registered_by',
        'first_name',
        'last_name',
        'email',
        'contact_number',
        'address',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /** Staff member who registered this customer */
    public function registeredBy()
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    /** Pets that belong to this customer */
    public function pets()
    {
        return $this->hasMany(Pet::class, 'customer_id');
    }

    /** Appointments linked to this customer */
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'customer_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    /** Full name helper */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}