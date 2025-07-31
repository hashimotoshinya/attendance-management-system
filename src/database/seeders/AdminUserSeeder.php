<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'admin',
                'password' => Hash::make('adminadmin'),
                'email_verified_at' => now(),
            ]
        );

        DB::table('admin_users')->updateOrInsert(
            ['email' => 'admin@example.com'],
            [
                'email' => 'admin@example.com',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::yesterday()->toDateString(),
            'start_time' => Carbon::yesterday()->setTime(9, 0),
            'end_time' => Carbon::yesterday()->setTime(18, 0),
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_time' => Carbon::yesterday()->setTime(12, 0),
            'end_time' => Carbon::yesterday()->setTime(12, 45),
        ]);
    }
}