<?php

namespace Tests\Feature\Attendance;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_is_displayed_as_off_duty()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }

    public function test_status_is_displayed_as_working()
    {
        $user = User::factory()->create();

        $this->setAttendanceStatus($user, 'working');

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    public function test_status_is_displayed_as_on_break()
    {
        $user = User::factory()->create();

        $this->setAttendanceStatus($user, 'on_break');

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    public function test_status_is_displayed_as_left_work()
    {
        $user = User::factory()->create();

        $this->setAttendanceStatus($user, 'left_work');

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }

    private function setAttendanceStatus(User $user, string $status): void
    {
        $now = now();

        switch ($status) {
            case 'working':
                Attendance::factory()->create([
                    'user_id' => $user->id,
                    'start_time' => $now,
                    'status' => '出勤中',
                ]);
                break;

            case 'on_break':
                $attendance = Attendance::factory()->create([
                    'user_id' => $user->id,
                    'start_time' => $now->subHour(),
                    'status' => '休憩中',
                ]);

                BreakTime::factory()->create([
                    'attendance_id' => $attendance->id,
                    'start_time' => $now->subMinutes(10),
                    'end_time' => null,
                ]);
                break;

            case 'left_work':
                Attendance::factory()->create([
                    'user_id' => $user->id,
                    'start_time' => $now->subHours(8),
                    'end_time' => $now->subHours(1),
                    'status' => '退勤済',
                ]);
                break;

            case 'off_duty':
            default:
                // 何もしない（勤務外）
                break;
        }
    }
}