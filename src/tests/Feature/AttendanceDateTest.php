<?php

namespace Tests\Feature;

use App\Models\User;

use Carbon\Carbon;

use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\TestCase;

class AttendanceDateTest extends TestCase

{

    use RefreshDatabase;

    public function test_現在の日付情報がUIと同じ形式で表示される()
    {
        Carbon::setTestNow('2026-07-01 09:00:00');
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->get(route('attendance.index'));
        $response->assertStatus(200);

        $response->assertSee('2026年7月1日');

        $response->assertSee('水');

        Carbon::setTestNow();
    }
}
 