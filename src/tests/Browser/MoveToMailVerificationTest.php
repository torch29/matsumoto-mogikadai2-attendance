<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;

class MoveToMailVerificationTest extends DuskTestCase
{
    /**
     * A basic browser test example.
     *
     * @return void
     */

    use DatabaseMigrations;

    //「認証はこちらから」リンクをクリックすると、メール認証サイトに遷移する
    public function testCanMoveEmailAuthenticationSiteWhenClickTheLink()
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create([
                'email_verified_at' => null,
            ]);
            $browser->loginAs($user);

            //リンクをクリックした際の遷移先を確認
            $browser->visit('/email/verify')
                ->assertSee('認証はこちらから')
                ->clickLink('認証はこちらから')
                ->pause(1000) //1秒待つ
                ->assertUrlIs('http://localhost:8025/');
        });
    }
}
