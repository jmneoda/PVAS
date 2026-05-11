<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentStatusHistory extends Model
{
    public $timestamps = false;            // table has no created_at / updated_at

    protected $table = 'appointment_status_histories';

    protected $fillable = [
        'appointment_id',
        'status',
        'changed_by',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    // ── Helper ────────────────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'no_show'  => 'No Show',
            'canceled' => 'Cancelled',
            default    => ucfirst($this->status),
        };
    }
}