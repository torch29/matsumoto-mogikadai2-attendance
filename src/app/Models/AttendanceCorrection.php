<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCorrection extends Model
{
    use HasFactory;

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    protected $fillable = [
        'attendance_id',
        'corrected_clock_in',
        'corrected_clock_out',
        'note',
        'approve_status',
    ];
}
