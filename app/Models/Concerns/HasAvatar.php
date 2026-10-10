<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

trait HasAvatar
{
    /**
     * Full display name. Models override via $displayNameColumns when needed.
     */
    public function getDisplayNameAttribute(): string
    {
        $full = trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));

        return $full !== '' ? $full : (string) ($this->name ?? $this->username ?? $this->email ?? 'User');
    }

    /**
     * Public URL of the profile picture, falling back to a generated initials avatar.
     */
    protected function avatarUrl(): Attribute
    {
        return Attribute::get(function () {
            $path = $this->profile_picture;

            if ($path) {
                return str_starts_with($path, 'http')
                    ? $path
                    : Storage::disk('public')->url($path);
            }

            return 'https://ui-avatars.com/api/?background=random&name=' . urlencode($this->display_name);
        });
    }
}
