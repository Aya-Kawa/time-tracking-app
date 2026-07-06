<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BreakTest extends TestCase
{
   use RefreshDatabase;

   public function test_休憩ボタンが正しく機能する()
   {
       Carbon::setTestNow('2026-06-29 12:00:00');

       $user = User::factory()->create();

       $attendance = AttendanceRecord::create([
           'user_id' => $user->id,
           'work_date' => today(),
           'clock_in' => now()->subHours(3),
           'clock_out' => null,
       ]);

       $response = $this->actingAs($user)
           ->post('/attendance/break-start');

       $response->assertRedirect('/attendance');

       $this->assertDatabaseHas('break_times', [
           'attendance_records_id' => $attendance->id,
           'end_time' => null,
       ]);

       Carbon::setTestNow();
   }

   public function test_休憩は一日に何回でもできる()
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
           'start_time' => now()->subHours(2),
           'end_time' => now()->subHours(1),
       ]);

       BreakTime::create([
           'attendance_records_id' => $attendance->id,
           'start_time' => now()->subMinutes(30),
           'end_time' => now()->subMinutes(10),
       ]);

       $this->assertEquals(
           2,
           BreakTime::where('attendance_records_id', $attendance->id)->count()
       );
   }

   public function test_休憩戻ボタンが正しく機能する()
   {
       Carbon::setTestNow('2026-06-29 13:00:00');

       $user = User::factory()->create();

       $attendance = AttendanceRecord::create([
           'user_id' => $user->id,
           'work_date' => today(),
           'clock_in' => now()->subHours(4),
           'clock_out' => null,
       ]);

       BreakTime::create([
           'attendance_records_id' => $attendance->id,
           'start_time' => now()->subMinutes(30),
           'end_time' => null,
       ]);

       $response = $this->actingAs($user)
           ->post('/attendance/break-end');

       $response->assertRedirect('/attendance');

       $this->assertDatabaseMissing('break_times', [
           'attendance_records_id' => $attendance->id,
           'end_time' => null,
       ]);

       Carbon::setTestNow();
   }

   public function test_休憩戻は一日に何回でもできる()
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
           'start_time' => now()->subHours(3),
           'end_time' => now()->subHours(2),
       ]);

       BreakTime::create([
           'attendance_records_id' => $attendance->id,
           'start_time' => now()->subHour(),
           'end_time' => now()->subMinutes(30),
       ]);

       $this->assertEquals(
           2,
           BreakTime::where('attendance_records_id', $attendance->id)
               ->whereNotNull('end_time')
               ->count()
       );
   }

   public function test_休憩時刻が勤怠一覧画面で確認できる()
   {
       $user = User::factory()->create();

       $attendance = AttendanceRecord::create([
           'user_id' => $user->id,
           'work_date' => today(),
           'clock_in' => now()->subHours(8),
           'clock_out' => now(),
       ]);

       BreakTime::create([
           'attendance_records_id' => $attendance->id,
           'start_time' => '2026-06-29 12:00:00',
           'end_time' => '2026-06-29 13:00:00',
       ]);

       $response = $this->actingAs($user)
           ->get('/attendance/list');

       $response->assertSee('1:00');
   }
} 