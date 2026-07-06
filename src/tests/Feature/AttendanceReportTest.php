<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\BreakTime;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_ゲストはレポートページにアクセスできない()
    {
        $response = $this->get('/attendance/report');

        $response->assertStatus(302);
    }

    public function test_認証ユーザーの統計情報が正しく計算される()
    {
        $user = User::factory()->create();

        $attendance = AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => '2026-06-29',
            'clock_in' => '2026-06-29 09:00:00',
            'clock_out' => '2026-06-29 18:00:00',
        ]);

        BreakTime::create([
            'attendance_records_id' => $attendance->id,
            'start_time' => '2026-06-29 12:00:00',
            'end_time' => '2026-06-29 13:00:00',
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance/report');

        $response->assertStatus(200);
        $response->assertSee('8h 0m');
    }

    public function test_勤怠記録がないユーザーでも安全に処理される()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/attendance/report');

        $response->assertStatus(200);
        $response->assertSee('0');
    }
}