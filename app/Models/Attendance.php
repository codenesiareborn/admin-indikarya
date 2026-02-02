<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'project_id',
        'tanggal',
        'check_in',
        'check_in_photo',
        'check_in_latitude',
        'check_in_longitude',
        'check_in_address',
        'check_out',
        'check_out_photo',
        'check_out_latitude',
        'check_out_longitude',
        'check_out_address',
        'jam_masuk_snapshot',
        'jam_pulang_snapshot',
        'status',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Alias for backward compatibility
    public function employee(): BelongsTo
    {
        return $this->user();
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'hadir' => 'Hadir',
            'terlambat' => 'Terlambat',
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            'alpha' => 'Alpha',
            default => $this->status,
        };
    }

    public function getCheckInPhotoUrlAttribute()
    {
        return $this->check_in_photo 
            ? asset('storage/' . $this->check_in_photo) 
            : null;
    }

    public function getCheckOutPhotoUrlAttribute()
    {
        return $this->check_out_photo 
            ? asset('storage/' . $this->check_out_photo) 
            : null;
    }

    public function getCheckInLocationAttribute()
    {
        if ($this->check_in_latitude && $this->check_in_longitude) {
            return [
                'latitude' => $this->check_in_latitude,
                'longitude' => $this->check_in_longitude,
                'maps_url' => "https://www.google.com/maps?q={$this->check_in_latitude},{$this->check_in_longitude}",
            ];
        }
        return null;
    }

    public function getCheckOutLocationAttribute()
    {
        if ($this->check_out_latitude && $this->check_out_longitude) {
            return [
                'latitude' => $this->check_out_latitude,
                'longitude' => $this->check_out_longitude,
                'maps_url' => "https://www.google.com/maps?q={$this->check_out_latitude},{$this->check_out_longitude}",
            ];
        }
        return null;
    }
}
