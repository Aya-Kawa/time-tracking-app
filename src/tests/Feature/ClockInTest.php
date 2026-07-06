<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClockInTest extends TestCase
{
   use RefreshDatabase;


   public function test_出勤ボタンが正しく機能する()
   {
       Carbon::setTestNow('2026-06-29 09:00:00');

       $user = User::factory()->create();

       $response = $this->actingAs($user)->post('/attendance/clock-in');

       $response->assertRedirect('/attendance');

       $this->assertDatabaseHas('attendance_records', [
           'user_id' => $user->id,
           'work_date' => '2026-06-29',
           'clock_in' => '2026-06-29 09:00:00',
       ]);

       Carbon::setTestNow();
   }

   public function test_出勤は一日一回のみできる()
{
   $user = User::factory()->create();

   AttendanceRecord::create([
       'user_id' => $user->id,
       'work_date' => today(),
       'clock_in' => now(),
       'clock_out' => null,
   ]);

   $response = $this->actingAs($user)->get('/attendance');

   $response->assertSee('出勤中');
   $response->assertSee('休憩入');
   $response->assertSee('退勤');
}

   public function test_出勤時刻が勤怠一覧画面で確認できる()
   {
       Carbon::setTestNow('2026-06-29 09:00:00');

       $user = User::factory()->create();

       AttendanceRecord::create([
           'user_id' => $user->id,
           'work_date' => '2026-06-29',
           'clock_in' => '2026-06-29 09:00:00',
           'clock_out' => null,
       ]);

       $response = $this->actingAs($user)->get('/attendance/list');

       $response->assertSee('09:00');

       Carbon::setTestNow();
   }
} 