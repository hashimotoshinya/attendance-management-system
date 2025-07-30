<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Facades\Session;

class AdminAttendanceDetailTest extends TestCase
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

    public function test_admin_can_view_attendance_show()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->withSession(['login_type' => 'admin'])
            ->get("/attendance/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee((string) $attendance->id);
    }

    public function test_validation_error_when_start_time_after_end_time()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->withSession(['login_type' => 'admin'])
            ->from("/attendance/{$attendance->id}")
            ->put("/attendance/{$attendance->id}", [
                'start_time' => '18:00',
                'end_time'   => '09:00',
                'note'       => '',
                'breaks'     => [],
            ]);

        $response->assertRedirect("/attendance/{$attendance->id}");
        $response->assertSessionHasErrors(['end_time', 'note']);
    }

    public function test_validation_error_when_note_is_empty()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->withSession(['login_type' => 'admin'])
            ->from("/attendance/{$attendance->id}")
            ->put("/attendance/{$attendance->id}", [
                'start_time' => '09:00',
                'end_time'   => '18:00',
                'note'       => '',
                'breaks'     => [],
            ]);

        $response->assertRedirect("/attendance/{$attendance->id}");
        $response->assertSessionHasErrors(['note']);
    }

    public function test_admin_can_submit_valid_attendance_correction()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->withSession(['login_type' => 'admin'])
            ->put("/attendance/{$attendance->id}", [
                'start_time' => '10:00',
                'end_time'   => '19:00',
                'note'       => 'Meeting delay',
                'breaks'     => [
                    ['start_time' => '12:00', 'end_time' => '13:00']
                ],
            ]);

        $response->assertRedirect(route('attendance.show', ['id' => $user->id]));
        $this->assertDatabaseHas('attendance_correct_requests', [
            'attendance_id' => $attendance->id,
            'note'          => 'Meeting delay',
            'status'        => 'pending',
        ]);
    }
}