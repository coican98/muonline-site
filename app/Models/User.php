<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'MEMB_INFO';
    protected $primaryKey = 'memb_guid';
    protected $keyType = 'int';
    public $incrementing = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'memb___id',
        'memb__pwd',
        'memb_name',
        'mail_addr',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'memb__pwd',
    ];

    public function getAuthIdentifierName()
    {
        return 'memb___id';
    }

    public function getAuthPasswordName()
    {
        return 'memb__pwd';
    }

    public function getAuthPassword()
    {
        return $this->memb__pwd;
    }

    public function getUsernameAttribute()
    {
        return $this->memb___id;
    }

    public function getNameAttribute($value)
    {
        return $value ?? $this->memb_name;
    }

    public function getGlobalAdminAttribute()
    {
        return in_array(strtolower((string) $this->memb___id), config('auth.admin_accounts', []), true);
    }
}
