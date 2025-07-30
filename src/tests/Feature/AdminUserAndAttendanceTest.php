<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminUserAndAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // 管理者ユーザーの作成とログイン
        $this->adminUser = User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('adminadmin'),
        ]);

        \DB::table('admin_users')->insert([
            'email' => 'admin@example.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($this->adminUser);
        session(['login_type' => 'admin']);
    }

    public function test_admin_can_see_all_general_users()
    {
        $users = User::factory()->count(3)->create();

        $response = $this->withSession(['login_type' => 'admin'])
            ->get('/admin/staff/list');

        $response->assertStatus(200);

        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
        }
    }

    public function test_admin_can_see_selected_user_attendance_list()
    {
        $user = User::factory()->create([
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::create(2025, 7, 29),
            'start_time' => Carbon::create(2025, 7, 29, 9, 0),
            'end_time' => Carbon::create(2025, 7, 29, 18, 0),
        ]);

        $response = $this->withSession(['login_type' => 'admin'])
            ->get("/admin/attendance/staff/{$user->id}");

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee($attendance->start_time->format('H:i'));
        $response->assertSee($attendance->end_time->format('H:i'));
        $weekdayMap = ['日', '月', '火', '水', '木', '金', '土'];
        $date = Carbon::parse($attendance->date);
        $weekday = $weekdayMap[$date->dayOfWeek];
        $formattedDate = $date->format('m/d') . "($weekday)";
        $response->assertSee($formattedDate);
    }

    public function test_admin_can_view_previous_month_attendance()
    {
        $user = User::factory()->create();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $lastMonth->toDateString(),
            'start_time' => Carbon::parse($lastMonth)->setTime(9, 0),
        ]);

        $response = $this->withSession(['login_type' => 'admin'])
            ->get("/admin/attendance/staff/{$user->id}?month=" . $lastMonth->format('Y-m'));

        $response->assertStatus(200);
        $weekdayMap = ['日', '月', '火', '水', '木', '金', '土'];
        $date = Carbon::parse($attendance->date);
        $weekday = $weekdayMap[$date->dayOfWeek];
        $formattedDate = $date->format('m/d') . "($weekday)";
        $response->assertSee($formattedDate);
    }

    public function test_admin_can_view_next_month_attendance()
    {
        $user = User::factory()->create();
        $nextMonth = Carbon::now()->addMonth()->startOfMonth();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $nextMonth->toDateString(),
            'start_time' => Carbon::parse($nextMonth)->setTime(10, 0),
        ]);

        $response = $this->withSession(['login_type' => 'admin'])
            ->get("/admin/attendance/staff/{$user->id}?month=" . $nextMonth->format('Y-m'));

        $response->assertStatus(200);
        $weekdayMap = ['日', '月', '火', '水', '木', '金', '土'];
        $date = Carbon::parse($attendance->date);
        $weekday = $weekdayMap[$date->dayOfWeek];
        $formattedDate = $date->format('m/d') . "($weekday)";
        $response->assertSee($formattedDate);
        $response->assertSee($attendance->start_time->format('H:i'));
    }

    public function test_admin_can_access_attendance_show_page_from_list()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::create(2025, 7, 15),
            'start_time' => Carbon::create(2025, 7, 15, 9, 0),
            'end_time' => Carbon::create(2025, 7, 15, 18, 0),
        ]);

        $response = $this->withSession(['login_type' => 'admin'])
            ->get("/attendance/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee($attendance->date->format('Y年n月j日'));
        $response->assertSee($attendance->start_time->format('H:i'));
        $response->assertSee($attendance->end_time->format('H:i'));
    }
}