<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

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
        if ($this->is_temporary_closed) return false;

        $today = now()->locale('id')->isoFormat('dddd'); 
        // Senin, Selasa, dst

        $schedule = $this->operatingHours()
            ->where('day', $today)
            ->first();

        if (!$schedule || !$schedule->is_open) return false;

        $now = now()->format('H:i:s');

        return $now >= $schedule->open_time &&
            $now <= $schedule->close_time;
    }
}
