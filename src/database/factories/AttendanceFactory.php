<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'date' => Carbon::today()->toDateString(),
            'start_time' => null,
            'end_time' => null,
            'status' => '勤務外',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function workingToday($userId)
    {
        return $this->state(function (array $attributes) use ($userId) {
            return [
                'user_id' => $userId,
                'date' => now()->toDateString(),
                'start_time' => now()->subHours(2),
                'status' => '出勤中',
            ];
        });
    }
}