<?php

namespace Tests\Feature;

use App\Models\AttendanceCorrection;
use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCorrectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_承認待ちの修正申請が全て表示されている()
    {
        $admin = User::factory()->create(['admin_status' => true]);
        $user = User::factory()->create(['name' => '山田太郎']);

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
            'remarks' => '電車遅延',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.correction.index') . '?status=pending');

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('電車遅延');
        $response->assertSee('承認待ち');
    }

    public function test_承認済みの修正申請が全て表示されている()
    {
        $admin = User::factory()->create(['admin_status' => true]);
        $user = User::factory()->create(['name' => '佐藤花子']);

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

        $response = $this->actingAs($admin)
            ->get(route('admin.correction.index') . '?status=approved');

        $response->assertStatus(200);
        $response->assertSee('佐藤花子');
        $response->assertSee('承認済み申請');
        $response->assertSee('承認済み');
    }

    public function test_修正申請の詳細内容が正しく表示されている()
    {
        $admin = User::factory()->create(['admin_status' => true]);
        $user = User::factory()->create(['name' => '田中一郎']);

        $attendance = AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => '2026-06-29',
            'clock_in' => '2026-06-29 09:00:00',
            'clock_out' => '2026-06-29 18:00:00',
        ]);

        $correction = AttendanceCorrection::create([
            'attendance_record_id' => $attendance->id,
            'user_id' => $user->id,
            'work_date' => '2026-06-29',
            'clock_in' => '2026-06-29 10:00:00',
            'clock_out' => '2026-06-29 19:00:00',
            'remarks' => '修正理由',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.correction.show', $correction->id));

        $response->assertStatus(200);
        $response->assertSee('田中一郎');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
        $response->assertSee('修正理由');
    }

    public function test_修正申請の承認処理が正しく行われる()
    {
        $admin = User::factory()->create(['admin_status' => true]);
        $user = User::factory()->create();

        $attendance = AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => '2026-06-29',
            'clock_in' => '2026-06-29 09:00:00',
            'clock_out' => '2026-06-29 18:00:00',
        ]);

        $correction = AttendanceCorrection::create([
            'attendance_record_id' => $attendance->id,
            'user_id' => $user->id,
            'work_date' => '2026-06-29',
            'clock_in' => '2026-06-29 10:00:00',
            'clock_out' => '2026-06-29 19:00:00',
            'remarks' => '承認します',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.correction.approve', $correction->id));

        $response->assertRedirect();

        $this->assertDatabaseHas('attendance_corrections', [
            'id' => $correction->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('attendance_records', [
            'id' => $attendance->id,
            'clock_in' => '2026-06-29 10:00:00',
            'clock_out' => '2026-06-29 19:00:00',
        ]);
    }
}