<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'username', 'email', 'password', 'active', 'meta',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Custom attributes
     * @var array
     */
    protected $appends = [
        'name', 'is_super_admin', 'is_user', 'last_login', 'penultimate_login',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Validation rules
     * @var array
     */
    public static $rules = [
        'first_name' => 'required|max:255',
        'last_name' => 'required|max:255',
        'username' => 'required|max:255|unique:users',
        'email' => 'required|email|max:255|unique:users',
        'password' => 'required|min:6|confirmed',
    ];

    /**
     * A user has many Login Activities
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function login_activities()
    {
        return $this->hasMany(LoginActivity::class);
    }

    /**
     * 'name' accessor
     * @return string
     */
    public function getNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * 'is_super_admin' accessor
     * @return bool
     */
    public function getIsSuperAdminAttribute()
    {
        $meta = json_decode($this->meta);
        return $meta && property_exists($meta, 'role') && strtolower($meta->role) === 'super administrator';
    }

    /**
     * 'is_user' accessor
     * @return bool
     */
    public function getIsUserAttribute()
    {
        $meta = json_decode($this->meta);
        return $meta && property_exists($meta, 'role') && strtolower($meta->role) === 'user';
    }

    /**
     * 'last_login' accessor
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\HasMany|object|null
     */
    public function getLastLoginAttribute()
    {
        return $this->login_activities()->where('success', 1)->orderBy('created_at', 'DESC')->first();
    }

    /**
     * 'penultimate_login' accessor
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\HasMany|object|null
     */
    public function getPenultimateLoginAttribute()
    {
        return $this->login_activities()->where('success', 1)->orderBy('created_at', 'DESC')->skip(1)->first();
    }

    /**
     * Get default user role
     * Basically, if we have existing users, the default role is user.
     * @return string
     */
    public static function getDefaultRole()
    {
        return static::first() ? 'User' : 'Super Administrator';
    }

}
