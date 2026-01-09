<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'role',
        'allowed_employees',
        'allowed_external_refs',
        'can_add_comments',
        'can_sync',
        'can_send_reminders',
        'is_active',
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
            'is_admin' => 'boolean',
            'allowed_employees' => 'array',
            'allowed_external_refs' => 'array',
            'can_add_comments' => 'boolean',
            'can_sync' => 'boolean',
            'can_send_reminders' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get all comments made by this user.
     */
    public function comments()
    {
        return $this->hasMany(InvoiceComment::class);
    }

    /**
     * Check if user has a specific role or one of multiple roles
     *
     * @param string|array $roles Role name(s) to check
     * @return bool
     */
    public function hasRole(string|array $roles): bool
    {
        if (is_array($roles)) {
            return in_array($this->role, $roles);
        }
        return $this->role === $roles;
    }

    /**
     * Check if user is an admin (either via is_admin flag or role)
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->is_admin || $this->role === 'admin';
    }

    /**
     * Check if user can view all invoices (no row-level filtering)
     *
     * @return bool
     */
    public function canViewAllInvoices(): bool
    {
        return $this->hasRole(['admin', 'manager']);
    }

    /**
     * Check if user can manage other users
     *
     * @return bool
     */
    public function canManageUsers(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Check if user can sync invoices from E-conomic API
     *
     * @return bool
     */
    public function canSync(): bool
    {
        return $this->can_sync || $this->isAdmin();
    }

    /**
     * Check if user can add comments to invoices
     *
     * @return bool
     */
    public function canAddComments(): bool
    {
        return $this->can_add_comments;
    }

    /**
     * Check if user can send reminder emails
     *
     * @return bool
     */
    public function canSendReminders(): bool
    {
        return $this->can_send_reminders || $this->isAdmin();
    }
}
