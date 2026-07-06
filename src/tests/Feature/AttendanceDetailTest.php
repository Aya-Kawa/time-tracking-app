<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\BreakTime;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceDetailTest extends TestCase
{
   use RefreshDatabase;


   public function test_勤怠詳細画面の名前がログインユーザーの氏名になっている()
   {
       $user = User::factory()->create([
           'name' => 'テスト太郎',
       ]);

       $attendance = AttendanceRecord::create([
           'user_id' => $user->id,
           'work_date' => '2026-06-29',
           'clock_in' => '2026-06-29 09:00:00',
           'clock_out' => '2026-06-29 18:00:00',
       ]);

       $response = $this->actingAs($user)
           ->get('/attendance/' . $attendance->id);

       $response->assertSee('テスト太郎');
   }

   public function test_勤怠詳細画面の日付が選択した日付になっている()
   {
       $user = User::factory()->create();

       $attendance = AttendanceRecord::create([
           'user_id' => $user->id,
           'work_date' => '2026-06-29',
           'clock_in' => '2026-06-29 09:00:00',
           'clock_out' => '2026-06-29 18:00:00',
       ]);

       $response = $this->actingAs($user)
           ->get('/attendance/' . $attendance->id);

       $response->assertSee('2026年');
       $response->assertSee('6月29日');
   }

   public function test_出勤退勤に記されている時間が打刻と一致している()
   {
       $user = User::factory()->create();

       $attendance = AttendanceRecord::create([
           'user_id' => $user->id,
           'work_date' => '2026-06-29',
           'clock_in' => '2026-06-29 09:00:00',
           'clock_out' => '2026-06-29 18:00:00',
       ]);

       $response = $this->actingAs($user)
           ->get('/attendance/' . $attendance->id);

       $response->assertSee('09:00');
       $response->assertSee('18:00');
   }

   public function test_休憩に記されている時間が打刻と一致している()
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
           ->get('/attendance/' . $attendance->id);

       $response->assertSee('12:00');
       $response->assertSee('13:00');
   }
} 