<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SampleUnitTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    /* Unitフォルダを維持するためのダミーテスト */
    public function test_access_successfully()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }
}
