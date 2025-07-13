<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Rest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //user.id [2～6] ※管理者以外の5名を指定
        $users = User::whereBetween('id', [2, 6])->pluck('id')->toArray();

        DB::transaction(function () {
            //user.id=2（テスト 一般職員さん）以外の人を指定して当日の勤怠1件目（休憩付き）を作成
            $todayAttendance1 = Attendance::factory()->create([
                'user_id' => 4,
                'date' => today(),
                'clock_in' => '07:50',
                'clock_out' => '18:00',
            ]);
            $rest1 = Rest::factory()->create([
                'attendance_id' => $todayAttendance1->id,
                'rest_start' => '11:30',
                'rest_end' => '12:20',
            ]);
        });
        //user.id=2（テスト 一般職員さん）以外の人を指定して当日の勤怠2件目（勤務中）を作成。
        $todayAttendance2 = Attendance::factory()->create([
            'user_id' => 5,
            'date' => today(),
            'clock_in' => '10:00',
        ]);
        //user.id=2（テスト 一般職員さん）の先月の勤怠を１件は作成されることを保証
        $subMonthAttendance = Attendance::factory()->create([
            'user_id' => 2,
            'date' => today()->subMonth(),
            'clock_in' => '9:00',
            'clock_out' => '17:30',
        ]);


        //前日以前の直近20日程度
        $dates = collect(range(1, 20))
            ->map(fn($i) => Carbon::today()->subDays($i))
            ->shuffle();
        $faker = Faker::create('ja_JP');

        //使用されたdate+user_idの組み合わせ、後で重複をスキップするために定義
        $usedPairs = [];
        $count = 0;
        $max = 40; //ダミーデータの作成件数

        while ($count < $max) {
            $date = $dates->random();
            $user_id = collect($users)->random();
            $key = $date->toDateString() . '-' . $user_id;

            if (in_array($key, $usedPairs)) {
                continue; //使用された組み合わせとの重複をスキップ
            }
            $attendance = Attendance::factory()->create([
                'user_id' => $user_id,
                'date' => $date,
            ]);

            //ここから休憩データの追加
            $clockIn = Carbon::parse($attendance->clock_in);
            $clockOut = Carbon::parse($attendance->clock_out);

            $restCount = rand(0, 2); //1勤務あたり0～2回休憩取得
            $canRestStart = Carbon::parse($clockIn); //最初の休憩可能時刻

            for ($i = 0; $i < $restCount; $i++) {
                if ($canRestStart >= $clockOut) {
                    break; //もう休憩はとれない
                }

                $restStart = Carbon::instance($faker->dateTimeBetween($canRestStart, $clockOut));
                $restEnd = (clone $restStart)->addMinutes(rand(10, 50));
                $latestRestEnd = (clone $clockOut)->subMinutes(10); //休憩終了時刻は退勤時刻の10分前を上限とする

                if ($restEnd > $latestRestEnd) {
                    $restEnd = clone $latestRestEnd;
                }

                Rest::factory()->create([
                    'attendance_id' => $attendance->id,
                    'rest_start' => $restStart,
                    'rest_end' => $restEnd,
                ]);

                //次の休憩はこのrest_end以降とれる
                $canRestStart = (clone $restEnd)->addMinutes(5);
            }

            $usedPairs[] = $key;
            $count++;
        }
    }
}
