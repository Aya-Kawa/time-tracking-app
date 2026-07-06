<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClockOutTest extends TestCase
{
   use RefreshDatabase;

   public function test_退勤ボタンが正しく機能する()
   {
       Carbon::setTestNow('2026-06-29 18:00:00');

       $user = User::factory()->create();

       AttendanceRecord::create([
           'user_id' => $user->id,
           'work_date' => today(),
           'clock_in' => now()->subHours(8),
           'clock_out' => null,
       ]);

       $response = $this->actingAs($user)
           ->post('/attendance/clock-out');

       $response->assertRedirect('/attendance');

       $this->assertDatabaseHas('attendance_records', [
           'user_id' => $user->id,
           'clock_out' => '2026-06-29 18:00:00',
       ]);

       Carbon::setTestNow();
   }

   

   public function test_退勤時刻が勤怠一覧画面で確認できる()
   {
       $user = User::factory()->create();

       AttendanceRecord::create([
           'user_id' => $user->id,
           'work_date' => today(),
           'clock_in' => '2026-06-29 09:00:00',
           'clock_out' => '2026-06-29 18:00:00',
       ]);

       $response = $this->actingAs($user)
           ->get('/attendance/list');

       $response->assertSee('18:00');
   }
} 