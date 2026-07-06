<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceListTest extends TestCase
{
   use RefreshDatabase;

   public function test_自分が行った勤怠情報が全て表示される()
   {
       Carbon::setTestNow('2026-06-15 09:00:00');

       $user = User::factory()->create();

       AttendanceRecord::create([
           'user_id' => $user->id,
           'work_date' => '2026-06-01',
           'clock_in' => '2026-06-01 09:00:00',
           'clock_out' => '2026-06-01 18:00:00',
       ]);

       AttendanceRecord::create([
           'user_id' => $user->id,
           'work_date' => '2026-06-02',
           'clock_in' => '2026-06-02 10:00:00',
           'clock_out' => '2026-06-02 19:00:00',
       ]);

       $response = $this->actingAs($user)->get('/attendance/list');

       $response->assertStatus(200);
       $response->assertSee('06/01');
       $response->assertSee('09:00');
       $response->assertSee('18:00');
       $response->assertSee('06/02');
       $response->assertSee('10:00');
       $response->assertSee('19:00');

       Carbon::setTestNow();
   }

   public function test_勤怠一覧画面に遷移した際に現在の月が表示される()
   {
       Carbon::setTestNow('2026-06-15 09:00:00');

       $user = User::factory()->create();

       $response = $this->actingAs($user)->get('/attendance/list');

       $response->assertStatus(200);
       $response->assertSee('2026/06');

       Carbon::setTestNow();
   }

   public function test_前月を押下した時に表示月の前月の情報が表示される()
   {
       Carbon::setTestNow('2026-06-15 09:00:00');

       $user = User::factory()->create();

       AttendanceRecord::create([
           'user_id' => $user->id,
           'work_date' => '2026-05-10',
           'clock_in' => '2026-05-10 09:00:00',
           'clock_out' => '2026-05-10 18:00:00',
       ]);

       $response = $this->actingAs($user)
           ->get('/attendance/list?month=2026-05');

       $response->assertStatus(200);
       $response->assertSee('2026/05');
       $response->assertSee('05/10');
       $response->assertSee('09:00');
       $response->assertSee('18:00');

       Carbon::setTestNow();
   }

   public function test_翌月を押下した時に表示月の翌月の情報が表示される()
   {
       Carbon::setTestNow('2026-06-15 09:00:00');

       $user = User::factory()->create();

       AttendanceRecord::create([
           'user_id' => $user->id,
           'work_date' => '2026-07-10',
           'clock_in' => '2026-07-10 09:00:00',
           'clock_out' => '2026-07-10 18:00:00',
       ]);

       $response = $this->actingAs($user)
           ->get('/attendance/list?month=2026-07');

       $response->assertStatus(200);
       $response->assertSee('2026/07');
       $response->assertSee('07/10');
       $response->assertSee('09:00');
       $response->assertSee('18:00');

       Carbon::setTestNow();
   }

   public function test_詳細を押下するとその日の勤怠詳細画面に遷移する()
   {
       $user = User::factory()->create();

       $attendance = AttendanceRecord::create([
           'user_id' => $user->id,
           'work_date' => today(),
           'clock_in' => now()->setTime(9, 0),
           'clock_out' => now()->setTime(18, 0),
       ]);

       $response = $this->actingAs($user)
           ->get(route('attendance.show', $attendance->id));

       $response->assertStatus(200);
       $response->assertSee('勤怠詳細');
   }
} 