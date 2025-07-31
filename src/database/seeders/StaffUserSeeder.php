<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class StaffUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'staff@example.com'],
            [
                'name' => 'staff',
                'password' => Hash::make('staffstaff'),
                'email_verified_at' => now(),
            ]
        );

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::yesterday()->toDateString(),
            'start_time' => Carbon::yesterday()->setTime(9, 30),
            'end_time' => Carbon::yesterday()->setTime(17, 30),
        ]);

        BreakTime::insert([
            [
                'attendance_id' => $attendance->id,
                'start_time' => Carbon::yesterday()->setTime(12, 15),
                'end_time' => Carbon::yesterday()->setTime(12, 45),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'attendance_id' => $attendance->id,
                'start_time' => Carbon::yesterday()->setTime(15, 0),
                'end_time' => Carbon::yesterday()->setTime(15, 10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}