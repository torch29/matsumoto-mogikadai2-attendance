<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rests()
    {
        return $this->hasMany(Rest::class);
    }

    public function attendanceCorrections()
    {
        return $this->hasMany(AttendanceCorrection::class);
    }

    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
    ];

    public function scopeTodayForUser($query, $userId)
    {
        return $query->where('user_id', $userId)
            ->whereDate('date', now()->toDateString());
    }

    public function getClockInFormattedAttribute()
    {
        if (!$this->clock_in) {
            return null;
        }

        return Carbon::parse($this->clock_in)->format('H:i');
    }

    public function getClockOutFormattedAttribute()
    {
        if (!$this->clock_out) {
            return null;
        }

        return Carbon::parse($this->clock_out)->format('H:i');
    }
}
