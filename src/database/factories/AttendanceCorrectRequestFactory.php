<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\AttendanceCorrectRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceCorrectRequestFactory extends Factory
{
    protected $model = AttendanceCorrectRequest::class;

    public function definition()
    {
        return [
            'attendance_id' => Attendance::factory(),
            'start_time' => '09:00',
            'end_time'   => '18:00',
            'note'       => $this->faker->sentence,
            'breaks'     => [['start_time' => '12:00', 'end_time' => '13:00']],
            'status'     => 'pending',
        ];
    }
}