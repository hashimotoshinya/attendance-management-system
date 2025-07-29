<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\AdminUser;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user1;
    protected $user2;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('users')->insert([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('adminadmin'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('admin_users')->insert([
            'email' => 'admin@example.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->admin = \App\Models\User::where('email', 'admin@example.com')->first();

        $this->user1 = User::factory()->create(['name' => '佐藤']);
        $this->user2 = User::factory()->create(['name' => '鈴木']);

        Attendance::factory()->create([
            'user_id' => $this->user1->id,
            'date' => today(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        Attendance::factory()->create([
            'user_id' => $this->user2->id,
            'date' => today(),
            'start_time' => '10:00:00',
            'end_time' => '19:00:00',
        ]);

        Attendance::factory()->create([
            'user_id' => $this->user1->id,
            'date' => today()->subDay(),
            'start_time' => '08:30:00',
            'end_time' => '17:30:00',
        ]);

        Attendance::factory()->create([
            'user_id' => $this->user2->id,
            'date' => today()->addDay(),
            'start_time' => '11:00:00',
            'end_time' => '20:00:00',
        ]);
    }

    private function actingAsAdmin()
    {
        Session::put('login_type', 'admin');
        return $this->actingAs($this->admin);
    }

    public function test_admin_can_see_today_attendance_list_with_correct_values()
    {
        $this->actingAsAdmin()
            ->get('/admin/attendance/list')
            ->assertStatus(200)
            ->assertSee(today()->format('Y年n月j日'))
            ->assertSee('佐藤')
            ->assertSee('09:00')
            ->assertSee('18:00')
            ->assertSee('鈴木')
            ->assertSee('10:00')
            ->assertSee('19:00');
    }

    public function test_admin_can_see_previous_day_attendance_list()
    {
        $this->actingAsAdmin()
            ->get('/admin/attendance/list?date=' . today()->subDay()->toDateString())
            ->assertStatus(200)
            ->assertSee(today()->subDay()->format('Y年n月j日'))
            ->assertSee('佐藤')
            ->assertSee('08:30')
            ->assertSee('17:30');
    }

    public function test_admin_can_see_next_day_attendance_list()
    {
        $this->actingAsAdmin()
            ->get('/admin/attendance/list?date=' . today()->addDay()->toDateString())
            ->assertStatus(200)
            ->assertSee(today()->addDay()->format('Y年n月j日'))
            ->assertSee('鈴木')
            ->assertSee('11:00')
            ->assertSee('20:00');
    }
}