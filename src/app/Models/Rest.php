<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rest extends Model
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
        'rest_start',
        'rest_end',
    ];

    /* 指定した型に型変換 */
    protected $casts = [
        'rest_start' => 'datetime',
        'rest_end' => 'datetime',
    ];
}
