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
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'id_role',
        'mobile_admin_access',
        'credential_key',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'credential_key',
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
            'mobile_admin_access' => 'boolean',
        ];
    }

    /** Boleh mengakses aplikasi Admin (mobile). */
    public function canAccessMobileAdmin(): bool
    {
        return (bool) $this->mobile_admin_access;
    }

    public function isSuperAdmin(): bool
    {
        return strtolower((string) optional($this->role)->nama) === 'superadmin';
    }

    /** Generate credential key unik untuk akses aplikasi admin. */
    public static function generateUniqueCredentialKey(): string
    {
        do {
            $key = 'MJ-ADM-' . strtoupper(\Illuminate\Support\Str::random(4)) . '-' . strtoupper(\Illuminate\Support\Str::random(3));
        } while (static::where('credential_key', $key)->exists());

        return $key;
    }

    public function adminLoginOtps()
    {
        return $this->hasMany(AdminLoginOtp::class);
    }

    public function account()
    {
        return $this->hasOne(DetailAccount::class, 'user_id', 'id');
    }

    public function assignedChatConversations()
    {
        return $this->hasMany(ChatConversation::class, 'assigned_admin_user_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'id_role', 'id_role');
    }

    public function userRole()
    {
        return $this->role->nama ?? null;
    }

    public function groupMenus()
    {
        $menus = [];
        if (($this->role->menus->count() ?? []) <= 0) return $menus;

        // Mengurutkan menus berdasarkan order pada module group dan module
        $sortedMenus = $this->role->menus->where('an', 1)->sortBy(function ($menu) {
            return $menu->module->group->order . '-' . $menu->module->order;
        });

        foreach ($sortedMenus as $menu) {
            $menus[$menu->module->group->nama][$menu->module->nama][] = $menu;
        }

        return $menus;
    }

    public function hasPermission(string $permission)
    {
        return $this->role->permissions()->where('permissions.name', $permission)->exists() ?? false;
    }

    public function createDetailAccount(array $param = [])
    {
        return $this->account()->create($param) ?? null;
    }

    public function updateDetailAccount(array $param = [])
    {
        return $this->account->update($param) ?? null;
    }
}
