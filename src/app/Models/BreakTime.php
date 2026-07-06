<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_records_id',
        'start_time',
        'end_time',
    ];
    public function attendance()
    {
        return $this->belongsTo(AttendanceRecord::class);
    }
}
