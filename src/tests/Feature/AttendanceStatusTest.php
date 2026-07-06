<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\AttendanceRecord;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_勤務外ステータスが表示される()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->get('/attendance');
        $response->assertSee('勤務外');
    }

    public function test_出勤中ステータスが表示される()
    {
        $user = User::factory()->create();
        AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now(),
            'clock_out' => null,
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance');
        $response->assertSee('出勤中');
    }

    public function test_休憩中ステータスが表示される()
    {
        $user = User::factory()->create();
        $attendance = AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now(),
            'clock_out' => null,
        ]);

        BreakTime::create([
            'attendance_records_id' => $attendance->id,
            'start_time' => now(),
            'end_time' => null,
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance');
        $response->assertSee('休憩中');
    }

    public function test_退勤済ステータスが表示される()
    {
        $user = User::factory()->create();
        AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now()->subHours(8),
            'clock_out' => now(),
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance');
        $response->assertSee('退勤済');
    }
}
 