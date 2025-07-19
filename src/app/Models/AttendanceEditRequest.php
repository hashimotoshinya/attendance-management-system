<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceEditRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'start_time',
        'end_time',
        'note',
        'breaks',
        'status',
    ];

    protected $casts = [
        'breaks' => 'array',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
