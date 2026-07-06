<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    public function test_その日に行われた全ユーザーの勤怠情報が確認できる()
    {
        Carbon::setTestNow('2026-06-29 09:00:00');

        $admin = User::factory()->create(['admin_status' => true]);
        $user1 = User::factory()->create(['name' => '山田太郎']);
        $user2 = User::factory()->create(['name' => '佐藤花子']);

        AttendanceRecord::create([
            'user_id' => $user1->id,
            'work_date' => '2026-06-29',
            'clock_in' => '2026-06-29 09:00:00',
            'clock_out' => '2026-06-29 18:00:00',
        ]);

        AttendanceRecord::create([
            'user_id' => $user2->id,
            'work_date' => '2026-06-29',
            'clock_in' => '2026-06-29 10:00:00',
            'clock_out' => '2026-06-29 19:00:00',
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('佐藤花子');
        $response->assertSee('09:00');
        $response->assertSee('10:00');

        Carbon::setTestNow();
    }

    public function test_遷移した際に現在の日付が表示される()
    {
        Carbon::setTestNow('2026-06-29 09:00:00');

        $admin = User::factory()->create(['admin_status' => true]);

        $response = $this->actingAs($admin)->get('/admin/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('2026/06/29');

        Carbon::setTestNow();
    }

    public function test_前日を押下した時に前の日の勤怠情報が表示される()
    {
        $admin = User::factory()->create(['admin_status' => true]);
        $user = User::factory()->create(['name' => '前日ユーザー']);

        AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => '2026-06-28',
            'clock_in' => '2026-06-28 09:00:00',
            'clock_out' => '2026-06-28 18:00:00',
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/list?date=2026-06-28');

        $response->assertStatus(200);
        $response->assertSee('2026/06/28');
        $response->assertSee('前日ユーザー');
    }

    public function test_翌日を押下した時に次の日の勤怠情報が表示される()
    {
        $admin = User::factory()->create(['admin_status' => true]);
        $user = User::factory()->create(['name' => '翌日ユーザー']);

        AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => '2026-06-30',
            'clock_in' => '2026-06-30 09:00:00',
            'clock_out' => '2026-06-30 18:00:00',
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/list?date=2026-06-30');

        $response->assertStatus(200);
        $response->assertSee('2026/06/30');
        $response->assertSee('翌日ユーザー');
    }
}