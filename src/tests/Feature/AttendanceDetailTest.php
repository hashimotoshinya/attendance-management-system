<?php

namespace Tests\Feature\Attendance;

use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendance_detail_displays_correct_user_name_and_date_and_times()
    {
        $user = User::factory()->create(['name' => '山田 太郎']);
        $attendanceDate = Carbon::parse('2024-05-10');

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $attendanceDate->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        BreakTime::factory()->createMany([
            [
                'attendance_id' => $attendance->id,
                'start_time' => '12:00:00',
                'end_time' => '12:30:00',
            ],
            [
                'attendance_id' => $attendance->id,
                'start_time' => '15:00:00',
                'end_time' => '15:15:00',
            ],
        ]);

        $response = $this->actingAs($user)->get(route('attendance.show', $attendance->id));

        $response->assertStatus(200);

        $response->assertSee('山田 太郎');

        $response->assertSee('2024年5月10日');

        $response->assertSee('09:00');
        $response->assertSee('18:00');

        $response->assertSee('12:00');
        $response->assertSee('12:30');
        $response->assertSee('15:00');
        $response->assertSee('15:15');
    }
}