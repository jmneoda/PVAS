<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Appointment extends Model
{
    protected $table = 'appointments';

    protected $fillable = [
        'customer_id',
        'pet_id',
        'veterinarian_id',
        'scheduled_date',
        'scheduled_time',
        'reason_for_visit',
        'type',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
    ];

    // ── Status constants ──────────────────────────────────────────────
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_CONFIRMED  = 'confirmed';
    const STATUS_COMPLETED  = 'completed';
    const STATUS_NO_SHOW    = 'no_show';
    const STATUS_CANCELED   = 'canceled';

    const STATUSES = [
        self::STATUS_SCHEDULED,
        self::STATUS_CONFIRMED,
        self::STATUS_COMPLETED,
        self::STATUS_NO_SHOW,
        self::STATUS_CANCELED,
    ];

    // ── Type constants ────────────────────────────────────────────────
    const TYPE_CHECKUP     = 'Checkup';
    const TYPE_VACCINATION = 'Vaccination';
    const TYPE_SURGERY     = 'Surgery';
    const TYPE_GROOMING    = 'Grooming';

    const TYPES = [
        self::TYPE_CHECKUP,
        self::TYPE_VACCINATION,
        self::TYPE_SURGERY,
        self::TYPE_GROOMING,
    ];

    public function isLocked(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    // ── Relationships ─────────────────────────────────────────────────

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function pet()
    {
        return $this->belongsTo(Pet::class, 'pet_id');
    }

    public function veterinarian()
    {
        return $this->belongsTo(User::class, 'veterinarian_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * All status-change history, oldest first.
     */
    public function statusHistories()
    {
        return $this->hasMany(AppointmentStatusHistory::class)->orderBy('changed_at');
    }

    // ── Helper: record a status history entry ─────────────────────────

    public function recordStatusHistory(?int $userId = null): void
    {
        $this->statusHistories()->create([
            'status'     => $this->status,
            'changed_by' => $userId ?? Auth::id(),
            'changed_at' => now(),
        ]);
    }

    // ── Query Scopes ──────────────────────────────────────────────────
    public function scopeScheduled($query) { return $query->where('status', self::STATUS_SCHEDULED); }
    public function scopeConfirmed($query) { return $query->where('status', self::STATUS_CONFIRMED); }
    public function scopeCompleted($query) { return $query->where('status', self::STATUS_COMPLETED); }
    public function scopeNoShow($query)    { return $query->where('status', self::STATUS_NO_SHOW); }
    public function scopeCancelled($query) { return $query->where('status', self::STATUS_CANCELED); }
}