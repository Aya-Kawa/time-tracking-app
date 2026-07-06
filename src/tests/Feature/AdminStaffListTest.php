<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStaffListTest extends TestCase
{
    use RefreshDatabase;

    public function test_管理者ユーザーが全一般ユーザーの氏名とメールアドレスを確認できる()
    {
        $admin = User::factory()->create([
            'admin_status' => true,
        ]);

        $user1 = User::factory()->create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
        ]);

        $user2 = User::factory()->create([
            'name' => '佐藤花子',
            'email' => 'sato@example.com',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.staff.list'));

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('yamada@example.com');
        $response->assertSee('佐藤花子');
        $response->assertSee('sato@example.com');
    }

    public function test_ユーザーの勤怠情報が正しく表示される()
    {
        Carbon::setTestNow('2026-06-15');

        $admin = User::factory()->create([
            'admin_status' => true,
        ]);

        $user = User::factory()->create([
            'name' => '山田太郎',
        ]);

        AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => '2026-06-15',
            'clock_in' => '2026-06-15 09:00:00',
            'clock_out' => '2026-06-15 18:00:00',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.staff.attendance.list', $user->id));

        $response->assertStatus(200);
        $response->assertSee('山田太郎さんの勤怠');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_前月を押下した時に表示月の前月の情報が表示される()
    {
        Carbon::setTestNow('2026-06-15');

        $admin = User::factory()->create([
            'admin_status' => true,
        ]);

        $user = User::factory()->create();

        AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => '2026-05-20',
            'clock_in' => '2026-05-20 09:00:00',
            'clock_out' => '2026-05-20 18:00:00',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.staff.attendance.list', $user->id) . '?month=2026-05');

        $response->assertStatus(200);
        $response->assertSee('2026年05月');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_翌月を押下した時に表示月の翌月の情報が表示される()
    {
        Carbon::setTestNow('2026-06-15');

        $admin = User::factory()->create([
            'admin_status' => true,
        ]);

        $user = User::factory()->create();

        AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => '2026-07-20',
            'clock_in' => '2026-07-20 09:00:00',
            'clock_out' => '2026-07-20 18:00:00',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.staff.attendance.list', $user->id) . '?month=2026-07');

        $response->assertStatus(200);
        $response->assertSee('2026年07月');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_詳細を押下するとその日の勤怠詳細画面に遷移する()
    {
        $admin = User::factory()->create([
            'admin_status' => true,
        ]);

        $user = User::factory()->create();

        $attendance = AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => '2026-06-15',
            'clock_in' => '2026-06-15 09:00:00',
            'clock_out' => '2026-06-15 18:00:00',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.attendance.show', $attendance->id));

        $response->assertStatus(200);
    }
}