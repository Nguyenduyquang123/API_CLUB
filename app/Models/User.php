<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'username', 'hashedPassword', 'email', 'displayName', 'avatarUrl', 'avatarId', 'bio', 'phone',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
    protected $hidden = [
        'hashedPassword',
    ];

    public function tokens()
    {
        return $this->hasMany(UserToken::class);
    }
    public function clubs()
    {
        return $this->hasMany(Club::class, 'owner_id');
    }
    public function clubMembers()
{
    return $this->hasMany(ClubMember::class, 'user_id', 'id');
}
}
