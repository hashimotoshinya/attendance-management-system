<?php

namespace Database\Factories;

use App\Models\BreakTime;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class BreakTimeFactory extends Factory
{
    protected $model = BreakTime::class;

    public function definition()
    {
        return [
            'attendance_id' => Attendance::factory(),
            'start_time' => Carbon::now()->subMinutes(10),
            'end_time' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}