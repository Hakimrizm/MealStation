<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'qris_image',
        'qris_name',
        'is_temporary_closed'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function menus()
    {
        return $this->hasMany(Menu::class);
    }

    public function operatingHours() { return $this->hasMany(OperatingHour::class); }

    public function isOpenNow()
    {
        $days = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
        ];

        $today = $days[now()->format('l')] ?? null;

        $schedule = $this->operatingHours()
            ->whereRaw('LOWER(TRIM(day)) = ?', [strtolower(trim($today))])
            ->where('is_open', 1)
            ->first();

        if (!$schedule) {
            return false;
        }

        // timezone Indonesia
        $now = Carbon::now('Asia/Jakarta');

        $open = Carbon::today('Asia/Jakarta')->setTimeFromTimeString($schedule->open_time);

        $close = Carbon::today('Asia/Jakarta')->setTimeFromTimeString($schedule->close_time);

        return $now->between($open, $close);
    }
}
