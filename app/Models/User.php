<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use App\Models\Preference;
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'level',
        'role',

        'is_suspended',
        'suspended_until',
        'is_banned',
        'banned_at',
        'profile_image',
    ];

    protected $appends = ['level', 'profile_picture'];

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

    public function getLevelAttribute()
    {
        return $this->attributes['level'] ?? 'level 0';
    }

    public function getProfilePictureAttribute()
    {
        $picture = $this->profilePicture()->first();

        return $picture ? Storage::url($picture->image_path) : null;
    }

    public function notifications()
    {
        return $this->hasMany(related: Notification::class);
    }

    public function feedback()
    {
        return $this->hasMany(Feedback::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function chatRooms()
    {
        return $this->hasMany(ChatRoom::class);
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function itemRequests()
    {
        return $this->hasMany(TradeRequest::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class, 'rated_user_id');
    }

    public function averageRating()
    {
        return $this->ratings()->avg('rating') ?? 0;
    }

    public function PassToken()
    {
        return $this->hasOne(NewPass::class);
    }

    public function reports()
    {
        return $this->hasMany(UserReport::class);
    }

    public function userTags()
    {
        return $this->hasOne(UserTag::class);

    }

    public function acquiredItems()
    {
        return $this->hasMany(AcquiredItem::class);
    }

    public function profilePicture()
    {
        return $this->hasOne(ProfilePicture::class);
    }

    public function preferences()
    {
        return $this->hasOne(Preference::class);
    }
}
