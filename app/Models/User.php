<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'password',
        'role_id',
        'created_by',
        'profile',
        'status',
        'company_id',
        'location'
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

    public function userRoles()
    {
        return $this->hasMany(UserRole::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function jobs()
    {
        return $this->belongsToMany(CompanyJob::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function companyJobUsers()
    {
        return $this->hasMany(CompanyJobUser::class, 'company_job_id', 'id');
    }

    public function companySummaries()
    {
        return $this->hasOne(CompanyJobSummary::class, 'company_id'); // Assuming company_id is the foreign key in company_summaries table
    }

    public function emailTemplate()
    {
        return $this->hasOne(EmailTemplate::class,'supplier_id');
    }

}
