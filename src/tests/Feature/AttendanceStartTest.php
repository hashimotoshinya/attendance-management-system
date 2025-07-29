<?php

namespace Tests\Feature\Attendance;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

class AttendanceStartTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_see_start_button_when_off_duty()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤');
    }

    public function test_user_can_start_work_and_status_changes_to_working()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/attendance/start');

        $response->assertRedirect('/attendance');

        $followUp = $this->get('/attendance');
        $followUp->assertSee('出勤中');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'date' => Carbon::today()->toDateString(),
            'start_time' => now()->format('H:i:s'),
        ]);
    }

    public function test_start_button_is_hidden_after_work_is_finished()
    {
        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'start_time' => now()->subHours(8),
            'end_time' => now()->subHours(1),
            'status' => '退勤済',
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertStatus(200);

        $response->assertDontSee('出勤');

        $response->assertSee('退勤済');
    }

    public function test_start_time_is_displayed_in_attendance_list()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post('/attendance/start');

        $response = $this->get('/attendance/list');
        $response->assertSee(Carbon::now()->format('H:i'));
    }
}