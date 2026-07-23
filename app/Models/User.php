<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Backend\Patient;
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable,HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'status',
        'password',
        'user_image',
        'cover_image',
        'address',
        'temp_password',
        'image',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


     public function patinet()
    {
        return $this->hasOne(Patient::class, 'patient_id');
    }

    /**
     * Get avatar URL attribute with fallbacks
     */
    public function getAvatarUrlAttribute()
    {
        $img = $this->user_image;
        if ($img) {
            if (file_exists(public_path('storage/user/' . $img))) {
                return asset('storage/user/' . $img);
            }
            if (file_exists(public_path('bookingsimage/' . $img))) {
                return asset('bookingsimage/' . $img);
            }
            if (file_exists(public_path('image/' . $img))) {
                return asset('image/' . $img);
            }
        }
        if (file_exists(public_path('storage/user/user.png'))) {
            return asset('storage/user/user.png');
        }
        return asset('image/user.png');
    }
}
