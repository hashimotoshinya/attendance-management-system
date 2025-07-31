<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrectRequest;

class AdminCorrectionRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $adminUser = User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('adminadmin'),
        ]);

        \DB::table('admin_users')->insert([
            'email' => 'admin@example.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($adminUser);
        Session::put('login_type', 'admin');
    }

    public function test_admin_can_view_all_pending_correction_requests()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        AttendanceCorrectRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'status' => 'pending',
        ]);

        $response = $this->withSession(['login_type' => 'admin'])
            ->get('/stamp_correction_request/list');

        $response->assertStatus(200);
        $response->assertSee('承認待ち');
        $response->assertSee($user->name);
    }

    public function test_admin_can_view_all_approved_correction_requests()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        AttendanceCorrectRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'status' => 'approved',
        ]);

        $response = $this->withSession(['login_type' => 'admin'])
            ->get('/stamp_correction_request/list');

        $response->assertStatus(200);
        $response->assertSee('承認済み');
        $response->assertSee($user->name);
    }

    public function test_admin_can_view_correction_request_detail()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        $correctionRequest = AttendanceCorrectRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'note' => '詳細内容',
        ]);

        $response = $this->withSession(['login_type' => 'admin'])
            ->get("/stamp_correction_request/{$correctionRequest->id}");

        $response->assertStatus(200);
        $response->assertSee('詳細内容');
    }

    public function test_admin_can_approve_correction_request()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $correctionRequest = AttendanceCorrectRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'start_time' => '10:00:00',
            'end_time' => '19:00:00',
            'note' => '修正内容',
            'breaks' => [
                ['start_time' => '12:00:00', 'end_time' => '13:00:00'],
            ],
        ]);

        $response = $this->withSession(['login_type' => 'admin'])
            ->put("/stamp_correction_request/{$correctionRequest->id}/approve");

        $response->assertRedirect("/stamp_correction_request/{$correctionRequest->id}");
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'start_time' => '10:00:00',
            'end_time' => '19:00:00',
            'note' => '修正内容',
        ]);

        $this->assertDatabaseHas('break_times', [
            'attendance_id' => $attendance->id,
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
        ]);

        $this->assertDatabaseHas('attendance_correct_requests', [
            'id' => $correctionRequest->id,
            'status' => 'approved',
        ]);
    }
}