<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;

class ExampleTest extends DuskTestCase
{
    /**
     * A basic browser test example.
     *
     * @return void
     */

    use DatabaseMigrations;

    //打刻画面にて現在の日時が表示されている
    public function testDisplayCurrentDateTime()
    {
        $staff = User::factory()->create();

        $this->browse(function (Browser $browser) use ($staff) {
            $browser->loginAs($staff)
                ->visit('/attendance')
                ->assertSee(now()->isoFormat('Y年 M月D日'))
                ->assertSee(now()->format('H:i'));
        });
    }
}
