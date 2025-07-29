<?php

namespace Tests\Feature\Attendance;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceEndTest extends TestCase
{
    use RefreshDatabase;

    public function test_end_button_appears_when_status_is_working_and_status_changes_to_finished()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::factory()->workingToday($user->id)->create();

        $response = $this->get('/attendance');
        $response->assertStatus(200)->assertSee('退勤');

        $response = $this->post('/attendance/end');
        $response->assertRedirect('/attendance');

        $response = $this->get('/attendance/list');
        $response->assertSee(now()->format('H:i'));
    }

    public function test_end_time_is_displayed_on_attendance_list()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $today = Carbon::today();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $today->toDateString(),
            'start_time' => $today->copy()->setTime(9, 0),
            'end_time' => $today->copy()->setTime(18, 0),
        ]);

        $response = $this->get('/attendance/list');
        $response->assertStatus(200)->assertSee('18:00');
    }
}