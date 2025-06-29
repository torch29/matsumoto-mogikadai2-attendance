<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Rest;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //[2, 3, 4, 5, 6]
        $users = User::whereBetween('id', [2, 6])->pluck('id')->toArray();
        //直近15日程度
        $dates = collect(range(0, 14))
            ->map(fn($i) => Carbon::today()->subDays($i))
            ->shuffle();
        $faker = Faker::create('ja_JP');

        //使用されたdate+user_idの組み合わせ
        $usedPairs = [];
        $count = 0;
        $max = 30; //ダミーデータの作成件数

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

            //休憩データの追加
            $clockIn = Carbon::parse($attendance->clock_in);
            $clockOut = Carbon::parse($attendance->clock_out);

            $restCount = rand(0, 2); //1勤務あたり0～2回休憩
            $canRestStart = Carbon::parse($clockIn); //最初の休憩可能時刻

            for ($i = 0; $i < $restCount; $i++) {
                if ($canRestStart >= $clockOut) {
                    break; //もう休憩はとれない
                }

                $restStart = Carbon::instance($faker->dateTimeBetween($canRestStart, $clockOut));
                $restEnd = (clone $restStart)->addMinutes(rand(10, 50));

                if ($restEnd > $clockOut) {
                    $restEnd = clone $clockOut;
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
