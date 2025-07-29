<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\AttendanceCorrectRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceCorrectionTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $attendance;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withExceptionHandling();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        session(['login_type' => 'staff']);

        $this->attendance = Attendance::factory()
            ->for($this->user)
            ->create([
                'date' => now()->toDateString(),
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
                'note' => '勤務',
            ]);

        BreakTime::factory()->create([
            'attendance_id' => $this->attendance->id,
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
        ]);
    }

    public function test_validation_error_when_start_time_is_after_end_time()
    {
        $this->actingAs($this->user)
            ->put(route('attendance.update', $this->attendance->id), [
                'start_time' => '19:00',
                'end_time' => '18:00',
                'note' => '修正',
            ])
            ->assertSessionHasErrors(['start_time']);
    }

    public function test_validation_error_when_break_start_time_is_after_work_end_time()
    {
        $this->actingAs($this->user)
            ->put(route('attendance.update', $this->attendance->id), [
                'start_time' => '09:00',
                'end_time' => '18:00',
                'note' => '修正',
                'breaks' => [
                    ['start_time' => '19:00', 'end_time' => '19:30'],
                ],
            ])
            ->assertSessionHasErrors(['breaks.0.start_time']);
    }

    public function test_validation_error_when_break_end_time_is_after_work_end_time()
    {
        $this->actingAs($this->user)
            ->put(route('attendance.update', $this->attendance->id), [
                'start_time' => '09:00',
                'end_time' => '18:00',
                'note' => '修正',
                'breaks' => [
                    ['start_time' => '17:00', 'end_time' => '19:00'],
                ],
            ])
            ->assertSessionHasErrors(['breaks.0.end_time']);
    }

    public function test_validation_error_when_note_is_missing()
    {
        $this->actingAs($this->user)
            ->put(route('attendance.update', $this->attendance->id), [
                'start_time' => '09:00',
                'end_time' => '18:00',
                'note' => '',
            ])
            ->assertSessionHasErrors(['note']);
    }

    public function test_attendance_correct_request_is_created_successfully()
    {
        $this->actingAs($this->user)
            ->put(route('attendance.update', $this->attendance->id), [
                'start_time' => '09:30',
                'end_time' => '18:00',
                'note' => '出勤時間修正',
                'breaks' => [],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('attendance_correct_requests', [
            'attendance_id' => $this->attendance->id,
            'start_time' => '09:30:00',
            'end_time' => '18:00:00',
            'note' => '出勤時間修正',
            'status' => 'pending',
        ]);
    }

    public function test_pending_requests_are_displayed_in_list()
    {
        AttendanceCorrectRequest::factory()->create([
            'attendance_id' => $this->attendance->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->user)
            ->get(route('stamp_correction_request.list'))
            ->assertSee('承認待ち');
    }

    public function test_approved_requests_are_displayed_in_list()
    {
        AttendanceCorrectRequest::factory()->create([
            'attendance_id' => $this->attendance->id,
            'status' => 'approved',
        ]);

        $this->actingAs($this->user)
            ->get(route('stamp_correction_request.list'))
            ->assertSee('承認済み');
    }

    public function test_can_access_attendance_correct_request_detail()
    {
        $request = AttendanceCorrectRequest::factory()->create([
            'attendance_id' => $this->attendance->id,
        ]);

        $this->actingAs($this->user)
            ->get(route('attendance.show', $request->attendance_id))
            ->assertStatus(200)
            ->assertSee($this->user->name);
    }
}