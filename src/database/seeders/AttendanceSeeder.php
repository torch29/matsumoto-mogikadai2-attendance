<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Models\Attendance;
use App\Models\User;
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

            Attendance::factory()->create([
                'user_id' => $user_id,
                'date' => $date,
            ]);

            $usedPairs[] = $key;
            $count++;
        }
    }
}
