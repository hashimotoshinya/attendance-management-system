<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'date', 'start_time', 'end_time', 'status'];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    public function breaks(): HasMany
    {
        return $this->hasMany(BreakTime::class);
    }

    public function getTotalWorkTimeAttribute(): ?string
    {
        if (!$this->start_time || !$this->end_time) return null;

        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);
        $totalMinutes = $end->diffInMinutes($start);

        $breakMinutes = $this->breaks->reduce(function ($carry, $break) {
            if ($break->start_time && $break->end_time) {
                $carry += Carbon::parse($break->end_time)->diffInMinutes(Carbon::parse($break->start_time));
            }
            return $carry;
        }, 0);

        $workingMinutes = $totalMinutes - $breakMinutes;

        return sprintf('%d:%02d', floor($workingMinutes / 60), $workingMinutes % 60);
    }

    public function getTotalBreakTimeAttribute(): ?string
    {
        $total = $this->breaks->reduce(function ($carry, $break) {
            if ($break->start_time && $break->end_time) {
                $carry += Carbon::parse($break->end_time)->diffInMinutes(Carbon::parse($break->start_time));
            }
            return $carry;
        }, 0);

        return $total > 0 ? sprintf('%02d:%02d', floor($total / 60), $total % 60) : '';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function editRequest()
    {
        return $this->hasOne(AttendanceEditRequest::class);
    }
}
