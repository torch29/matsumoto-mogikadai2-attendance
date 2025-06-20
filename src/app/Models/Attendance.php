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

    //指定した型に型変換
    protected $casts = [
        'date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
    ];

    // 指定されたスタッフの「本日分」の勤怠データ（休憩情報含む）を取得する
    public function scopeTodayForUser($query, $userId)
    {
        return $query->where('user_id', $userId)
            ->whereDate('date', now()->toDateString())->with('rests');
    }

    // 指定された「日付」の、全ユーザの勤怠一覧を取得
    public function scopeTodayAttendance($query, $date)
    {
        return $query->with(['user', 'rest'])
            ->whereDate('clock_in', $date)
            ->orderBy(User::select('name')->whereColumn('users.id', 'attendances.user_id'));
    }

    //休憩時間の合計を計算
    public function getTotalRestSecondsAttribute()
    {
        return $this->rests->sum(
            function ($rest) {
                if ($rest->rest_start && $rest->rest_end) {
                    return Carbon::parse($rest->rest_end)->diffInSeconds(Carbon::parse($rest->rest_start));
                }
                return 0;
            }
        );
    }

    //合計休憩時間のフォーマット
    public function getTotalRestFormattedAttribute()
    {
        $totalRestSeconds = $this->total_rest_seconds;
        return $totalRestSeconds > 0
            ? Carbon::createFromTime(0, 0)->addSeconds($totalRestSeconds)->isoFormat('H:mm')
            : null;
    }

    //実労働時間の合計を計算
    public function getTotalWorkSecondsAttribute()
    {
        if (!$this->clock_in || !$this->clock_out) {
            return null;
        }

        $workSeconds = Carbon::parse($this->clock_out)->diffInSeconds(Carbon::parse($this->clock_in));
        $restSeconds = $this->total_rest_seconds;

        return $workSeconds - $restSeconds;
    }

    //実労働時間のフォーマット
    public function getTotalWorkFormattedAttribute()
    {
        $totalWorkMinutes = floor($this->total_work_seconds / 60);
        if ($totalWorkMinutes === null) {
            return null;
        }

        if ($totalWorkMinutes >= 0) {
            return Carbon::createFromTime(0, 0)
                ->addMinutes($totalWorkMinutes)
                ->isoFormat('H:mm');
        } else {
            $absolute = abs($totalWorkMinutes);
            return '-' . Carbon::createFromTime(0, 0)
                ->addMinutes($absolute)
                ->isoFormat('H:mm');
        }
    }

    //出勤時刻のフォーマット
    public function getClockInFormattedAttribute()
    {
        if (!$this->clock_in) {
            return null;
        }

        return Carbon::parse($this->clock_in)->format('H:i');
    }

    //退勤時刻のフォーマット
    public function getClockOutFormattedAttribute()
    {
        if (!$this->clock_out) {
            return null;
        }

        return Carbon::parse($this->clock_out)->format('H:i');
    }

    //前月を返す
    public static function getPreviousMonth($baseDate)
    {
        return Carbon::parse($baseDate)->subMonthNoOverflow()->format('Y-m');
    }

    //翌月を返す
    public static function getNextMonth($baseDate)
    {
        return Carbon::parse($baseDate)->addMonthNoOverflow()->format('Y-m');
    }
}
