<?php

namespace Tests\Feature\Attendance;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_see_own_attendance_records()
    {
        $user = User::factory()->create();
        $attendances = Attendance::factory()
            ->count(3)
            ->sequence(
                ['date' => Carbon::now()->startOfMonth()->toDateString()],
                ['date' => Carbon::now()->startOfMonth()->addDay()->toDateString()],
                ['date' => Carbon::now()->startOfMonth()->addDays(2)->toDateString()]
            )
            ->for($user)
            ->create();

        $this->actingAs($user)
            ->get('/attendance/list')
            ->assertStatus(200)
            ->assertSeeInOrder($attendances->pluck('date')->map(fn($d) => Carbon::parse($d)->format('m/d'))->toArray());
    }

    public function test_current_month_is_displayed()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/attendance/list')
            ->assertStatus(200)
            ->assertSee(Carbon::now()->format('Y年m月'));
    }

    public function test_previous_month_button_displays_previous_month()
    {
        $user = User::factory()->create();
        $previousMonth = Carbon::now()->subMonth();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $previousMonth->startOfMonth()->toDateString(),
        ]);

        $this->actingAs($user)
            ->get('/attendance/list?month=' . $previousMonth->format('Y-m'))
            ->assertStatus(200)
            ->assertSee($previousMonth->format('Y年m月'));
    }

    public function test_next_month_button_displays_next_month()
    {
        $user = User::factory()->create();
        $nextMonth = Carbon::now()->addMonth();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $nextMonth->startOfMonth()->toDateString(),
        ]);

        $this->actingAs($user)
            ->get('/attendance/list?month=' . $nextMonth->format('Y-m'))
            ->assertStatus(200)
            ->assertSee($nextMonth->format('Y年m月'));
    }

    public function test_detail_link_navigates_to_detail_page()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::now()->toDateString(),
        ]);

        $this->actingAs($user)
            ->get('/attendance/list')
            ->assertStatus(200)
            ->assertSee(route('attendance.show', ['id' => $attendance->id]));
    }
}