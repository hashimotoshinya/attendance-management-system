<?php

namespace Tests\Feature\Attendance;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

class BreakTimeFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_break_button_is_visible_when_user_is_working()
    {
        $user = User::factory()->create();
        Attendance::factory()->workingToday($user->id)->create();
        $this->actingAs($user);

        $response = $this->get('/attendance');
        $response->assertSee('休憩');
    }

    public function test_user_can_start_and_end_break_multiple_times()
    {
        $user = User::factory()->create();
        Attendance::factory()->workingToday($user->id)->create();
        $this->actingAs($user);

        $this->post('/attendance/break/start');
        $this->assertDatabaseHas('break_times', [
            'attendance_id' => Attendance::first()->id,
            'end_time' => null,
        ]);

        $this->post('/attendance/break/end');
        $this->assertDatabaseMissing('break_times', [
            'attendance_id' => Attendance::first()->id,
            'end_time' => null,
        ]);

        $this->post('/attendance/break/start');
        $this->assertDatabaseHas('break_times', [
            'attendance_id' => Attendance::first()->id,
            'end_time' => null,
        ]);
    }

    public function test_status_changes_to_breaking_and_back_to_working()
    {
        $user = User::factory()->create();
        Attendance::factory()->workingToday($user->id)->create();
        $this->actingAs($user);

        $this->post('/attendance/break/start');
        $this->assertEquals('休憩中', Attendance::first()->fresh()->status);

        $this->post('/attendance/break/end');
        $this->assertEquals('出勤中', Attendance::first()->fresh()->status);
    }

    public function test_break_end_button_is_visible_during_break()
    {
        $user = User::factory()->create();
        Attendance::factory()->workingToday($user->id)->create([
            'status' => '休憩中',
        ]);
        $this->actingAs($user);

        $response = $this->get('/attendance');
        $response->assertSee('休憩戻');
    }

    public function test_break_time_is_displayed_on_attendance_list()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->workingToday($user->id)->create();

        $attendance->breaks()->create([
            'start_time' => now()->subMinutes(30),
            'end_time' => now()->subMinutes(15),
        ]);

        $this->actingAs($user);
        $response = $this->get('/attendance/list');

        $response->assertSee('00:15');
    }
}