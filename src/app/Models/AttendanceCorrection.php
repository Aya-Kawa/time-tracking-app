<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCorrection extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_record_id',
        'user_id',
        'work_date',
        'clock_in',
        'clock_out',
        'remarks',
        'status',
    ];

    public function attendanceRecord()
    {
        return $this->belongsTo(AttendanceRecord::class);
    }

    public function breakTimes()
    {
        return $this->hasMany(AttendanceCorrectionBreak::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
