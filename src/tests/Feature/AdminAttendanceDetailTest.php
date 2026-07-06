<?php
namespace Tests\Feature;
use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;
    public function test_勤怠詳細画面に表示されるデータが選択したものになっている()
    {
        $admin = User::factory()->create(['admin_status' => true]);
        $user = User::factory()->create(['name' => '山田太郎']);
        $attendance = AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => '2026-06-29',
            'clock_in' => '2026-06-29 09:00:00',
            'clock_out' => '2026-06-29 18:00:00',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.attendance.show', $attendance->id));

        $response->assertStatus(200);

        $response->assertSee('山田太郎');

        $response->assertSee('2026年');

        $response->assertSee('6月29日');

        $response->assertSee('09:00');

        $response->assertSee('18:00');
    }

    public function test_出勤時間が退勤時間より後の場合エラーメッセージが表示される()
    {
        $admin = User::factory()->create(['admin_status' => true]);
        $user = User::factory()->create();
        $attendance = AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => '2026-06-29',
            'clock_in' => '2026-06-29 09:00:00',
            'clock_out' => '2026-06-29 18:00:00',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.attendance.update', $attendance->id), [
                'clock_in' => '19:00',
                'clock_out' => '18:00',
                'remarks' => '修正します',
            ]);

        $response->assertSessionHasErrors();
    }

    public function test_休憩開始時間が退勤時間より後の場合エラーメッセージが表示される()
    {
        $admin = User::factory()->create(['admin_status' => true]);
        $user = User::factory()->create();
        $attendance = AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => '2026-06-29',
            'clock_in' => '2026-06-29 09:00:00',
            'clock_out' => '2026-06-29 18:00:00',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.attendance.update', $attendance->id), [
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
        $admin = User::factory()->create(['admin_status' => true]);
        $user = User::factory()->create();
        $attendance = AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => '2026-06-29',
            'clock_in' => '2026-06-29 09:00:00',
            'clock_out' => '2026-06-29 18:00:00',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.attendance.update', $attendance->id), [
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
        $admin = User::factory()->create(['admin_status' => true]);
        $user = User::factory()->create();
        $attendance = AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => '2026-06-29',
            'clock_in' => '2026-06-29 09:00:00',
            'clock_out' => '2026-06-29 18:00:00',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.attendance.update', $attendance->id), [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'remarks' => '',
            ]);

        $response->assertSessionHasErrors([

            'remarks' => '備考を記入してください。',
        ]);
    }

    public function test_修正処理が実行される()
    {
        $admin = User::factory()->create(['admin_status' => true]);
        $user = User::factory()->create();
        $attendance = AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => '2026-06-29',
            'clock_in' => '2026-06-29 09:00:00',
            'clock_out' => '2026-06-29 18:00:00',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.attendance.update', $attendance->id), [
                'start_time' => '10:00',
                'end_time' => '19:00',
                'remarks' => '管理者修正',
            ]);

            $response->assertSessionHasNoErrors();


        $this->assertDatabaseHas('attendance_corrections', [
            'attendance_record_id' => $attendance->id,
            'user_id' => $user->id,
            'remarks' => '管理者修正',
            'status' => 'pending',
        ]);
    }
}
