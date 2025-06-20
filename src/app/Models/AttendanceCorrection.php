<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AttendanceCorrection extends Model
{
    use HasFactory;

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function restCorrections()
    {
        return $this->hasMany(RestCorrection::class);
    }

    protected $fillable = [
        'attendance_id',
        'corrected_clock_in',
        'corrected_clock_out',
        'note',
        'approve_status',
    ];

    protected $casts = [
        'corrected_clock_in' => 'datetime',
        'corrected_clock_out' => 'datetime',
    ];

    public function approvalStatusLabel(): string
    {
        $labels = [
            'pending' => '承認待ち',
            'completed' => '承認済み',
        ];
        return $labels[$this->approve_status] ?? '不明';
    }

    public function getCorrectionTargetDateFormattedAttribute()
    {
        if (!$this->attendance->date) {
            return null;
        }

        return Carbon::parse($this->attendance->date)->format('Y/m/d');
    }

    public function getCorrectionClockInFormattedAttribute()
    {
        if (!$this->corrected_clock_in) {
            return null;
        }

        return Carbon::parse($this->corrected_clock_in)->format('H:i');
    }

    public function getCorrectionClockOutFormattedAttribute()
    {
        if (!$this->corrected_clock_out) {
            return null;
        }

        return Carbon::parse($this->clock_out)->format('H:i');
    }

    public function getRequestedAtFormattedAttribute()
    {
        if (!$this->created_at) {
            return null;
        }

        return Carbon::parse($this->created_at)->format('Y/m/d');
    }
}
