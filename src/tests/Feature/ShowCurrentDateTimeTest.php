<?php

namespace Tests\Feature\Attendance;

use Tests\TestCase;
use Illuminate\Support\Carbon;
use App\Models\User;

class ShowCurrentDateTimeTest extends TestCase
{
    public function test_current_datetime_is_displayed_correctly()
    {
        Carbon::setTestNow(Carbon::create(2025, 7, 17, 10, 15));

        $this->actingAs(User::factory()->create());

        $response = $this->get('/attendance');

        $weekDays = ['日', '月', '火', '水', '木', '金', '土'];
        $now = Carbon::now();
        $date = $now->format('Y年n月j日') . '(' . $weekDays[$now->dayOfWeek] . ')';
        $time = $now->format('H:i');

        $response->assertStatus(200);
        $response->assertSee($date);
        $response->assertSee($time);
    }
}