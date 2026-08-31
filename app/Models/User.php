<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use App\Enums\RoleEnum;
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
        'current_org_id'
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

    /*
    |--------------------------------------------------------------------------
    | Organizations
    |--------------------------------------------------------------------------
    | ユーザーが所属しているOrganization
    */
    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(
            Organization::class,
            'organization_user'
        )->withPivot('role_id')
            ->withTimestamps();
    }

    /*
    |--------------------------------------------------------------------------
    | Current Organization
    |--------------------------------------------------------------------------
    | 現在操作しているOrganization
    */
    public function currentOrganization(): BelongsTo
    {
        return $this->belongsTo(
            Organization::class,
            'current_org_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Current Role
    |--------------------------------------------------------------------------
    | 現在のOrganizationでのRole
    */
    public function currentRole(): ?Role
    {
        $membership = $this->organizations
            ->firstWhere('id', $this->current_org_id);

        if (!$membership) {
            return null;
        }

        return Role::find($membership->pivot->role_id);
    }

    /*
    |--------------------------------------------------------------------------
    | Role Checks
    |--------------------------------------------------------------------------
    */

    public function isOwner(): bool
    {
        return $this->currentRole()?->name === RoleEnum::OWNER->value;
    }

    public function isAdmin(): bool
    {
        return $this->currentRole()?->name === RoleEnum::ADMIN->value;
    }

    public function isMember(): bool
    {
        return $this->currentRole()?->name === RoleEnum::MEMBER->value;
    }

    public function assignedTasks()
    {
        return $this->hasMany(Task::class, 'assigned_user_id');
    }

    public function createdTasks()
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    public function comments()
    {
        return $this->hasMany(TaskComment::class);
    }

    public function notifications()
    {
        return $this->hasMany(UserNotification::class);
    }
}
