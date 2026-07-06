<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\AttendanceCorrection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceCorrectionTest extends TestCase
{
   use RefreshDatabase;

   public function test_出勤時間が退勤時間より後の場合エラーメッセージが表示される()
   {
       $user = User::factory()->create();

       $attendance = AttendanceRecord::create([
           'user_id' => $user->id,
           'work_date' => '2026-06-29',
           'clock_in' => '2026-06-29 09:00:00',
           'clock_out' => '2026-06-29 18:00:00',
       ]);

       $response = $this->actingAs($user)
           ->post('/attendance/' . $attendance->id . '/correction', [
               'start_time' => '19:00',
               'end_time' => '18:00',
               'remarks' => '修正します',
           ]);

       $response->assertSessionHasErrors([
           'end_time' => '出勤時間もしくは退勤時間が不適切な値です。',
       ]);
   }

   public function test_休憩開始時間が退勤時間より後の場合エラーメッセージが表示される()
   {
       $user = User::factory()->create();

       $attendance = AttendanceRecord::create([
           'user_id' => $user->id,
           'work_date' => '2026-06-29',
           'clock_in' => '2026-06-29 09:00:00',
           'clock_out' => '2026-06-29 18:00:00',
       ]);

       $response = $this->actingAs($user)
           ->post('/attendance/' . $attendance->id . '/correction', [
               'clock_in' => '09:00',
               'clock_out' => '18:00',
               'breaks' => [
                   [
                       'start_time' => '19:00',
                       'end_time' => '19:30',
                   ],
               ],
               'remarks' => '修正します',
           ]);

       $response->assertSessionHasErrors();
   }

   public function test_休憩終了時間が退勤時間より後の場合エラーメッセージが表示される()
   {
       $user = User::factory()->create();

       $attendance = AttendanceRecord::create([
           'user_id' => $user->id,
           'work_date' => '2026-06-29',
           'clock_in' => '2026-06-29 09:00:00',
           'clock_out' => '2026-06-29 18:00:00',
       ]);

       $response = $this->actingAs($user)
           ->post('/attendance/' . $attendance->id . '/correction', [
               'clock_in' => '09:00',
               'clock_out' => '18:00',
               'breaks' => [
                   [
                       'start_time' => '17:30',
                       'end_time' => '19:00',
                   ],
               ],
               'remarks' => '修正します',
           ]);

       $response->assertSessionHasErrors();
   }

   public function test_備考欄が未入力の場合エラーメッセージが表示される()
   {
       $user = User::factory()->create();

       $attendance = AttendanceRecord::create([
           'user_id' => $user->id,
           'work_date' => '2026-06-29',
           'clock_in' => '2026-06-29 09:00:00',
           'clock_out' => '2026-06-29 18:00:00',
       ]);

       $response = $this->actingAs($user)
           ->post('/attendance/' . $attendance->id . '/correction', [
               'clock_in' => '09:00',
               'clock_out' => '18:00',
               'remarks' => '',
           ]);

       $response->assertSessionHasErrors([
           'remarks' => '備考を記入してください。',
       ]);
   }

   public function test_修正申請処理が実行される()
   {
       $user = User::factory()->create();

       $attendance = AttendanceRecord::create([
           'user_id' => $user->id,
           'work_date' => '2026-06-29',
           'clock_in' => '2026-06-29 09:00:00',
           'clock_out' => '2026-06-29 18:00:00',
       ]);

       $response = $this->actingAs($user)
           ->post('/attendance/' . $attendance->id . '/correction', [
               'start_time' => '10:00',
               'end_time' => '19:00',
               'remarks' => '電車遅延のため',
           ]);

       $this->assertDatabaseHas('attendance_corrections', [
           'attendance_record_id' => $attendance->id,
           'user_id' => $user->id,
           'remarks' => '電車遅延のため',
           'status' => 'pending',
       ]);
   }

   public function test_承認待ちにログインユーザーが行った申請が全て表示される()
   {
       $user = User::factory()->create();

       $attendance = AttendanceRecord::create([
           'user_id' => $user->id,
           'work_date' => '2026-06-29',
           'clock_in' => '2026-06-29 09:00:00',
           'clock_out' => '2026-06-29 18:00:00',
       ]);

       AttendanceCorrection::create([
           'attendance_record_id' => $attendance->id,
           'user_id' => $user->id,
           'work_date' => '2026-06-29',
           'clock_in' => '2026-06-29 10:00:00',
           'clock_out' => '2026-06-29 19:00:00',
           'remarks' => '電車遅延のため',
           'status' => 'pending',
       ]);

       $response = $this->actingAs($user)
           ->get('/stamp_correction_request/list?status=pending');

       $response->assertSee('電車遅延のため');
       $response->assertSee('承認待ち');
   }

   public function test_承認済みに管理者が承認した申請が全て表示される()
   {
       $user = User::factory()->create();

       $attendance = AttendanceRecord::create([
           'user_id' => $user->id,
           'work_date' => '2026-06-29',
           'clock_in' => '2026-06-29 09:00:00',
           'clock_out' => '2026-06-29 18:00:00',
       ]);

       AttendanceCorrection::create([
           'attendance_record_id' => $attendance->id,
           'user_id' => $user->id,
           'work_date' => '2026-06-29',
           'clock_in' => '2026-06-29 10:00:00',
           'clock_out' => '2026-06-29 19:00:00',
           'remarks' => '承認済み申請',
           'status' => 'approved',
       ]);

       $response = $this->actingAs($user)
           ->get('/stamp_correction_request/list?status=approved');

       $response->assertSee('承認済み申請');
       $response->assertSee('承認済み');
   }

   public function test_各申請の詳細を押すと勤怠詳細画面に遷移する()
{
   $user = User::factory()->create();

   $attendance = AttendanceRecord::create([
       'user_id' => $user->id,
       'work_date' => now()->toDateString(),
       'clock_in' => now()->setTime(9, 0),
       'clock_out' => now()->setTime(18, 0),
   ]);

   AttendanceCorrection::create([
       'attendance_record_id' => $attendance->id,
       'user_id' => $user->id,
       'work_date' => $attendance->work_date,
       'clock_in' => '2026-06-29 09:00',
       'clock_out' => '2026-06-29 18:00',
       'remarks' => '修正申請',
       'status' => 'pending',
   ]);

   $response = $this->actingAs($user)
       ->get('/stamp_correction_request/list');

   $response->assertStatus(200);

   $response = $this->actingAs($user)
       ->get('/attendance/' . $attendance->id);

   $response->assertStatus(200);
} 
} 