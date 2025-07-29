<?php

namespace Database\Factories;

use App\Models\StampCorrectionRequest;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class StampCorrectionRequestFactory extends Factory
{
    protected $model = StampCorrectionRequest::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'attendance_id' => Attendance::factory(),
            'requested_start_time' => Carbon::parse('09:00'),
            'requested_end_time' => Carbon::parse('18:00'),
            'requested_note' => '修正希望です',
            'status' => 'approve',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}