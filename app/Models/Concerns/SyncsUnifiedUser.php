<?php

namespace App\Models\Concerns;

use App\Models\User;

/**
 * For module profiles (SupportTeam, SolveUser): keeps the linked row in `users`
 * (the single credential store) in step whenever the profile is saved.
 */
trait SyncsUnifiedUser
{
    /** @return array{name: string, email: string, username: ?string} */
    abstract protected function unifiedIdentity(): array;

    public static function bootSyncsUnifiedUser(): void
    {
        static::saved(function ($profile) {
            $identity = $profile->unifiedIdentity();

            $user = ($profile->user_id ? User::find($profile->user_id) : null)
                ?? User::where('email', $identity['email'])->first()
                ?? new User();

            $user->name = $identity['name'];
            $user->email = $identity['email'];
            $user->username = $identity['username'];

            // The profile attribute is already hashed (via its cast); the User cast leaves hashes alone.
            if (! $user->exists || $profile->wasChanged('password') || $profile->wasRecentlyCreated) {
                $user->password = $profile->getAttributes()['password'];
            }

            $user->save();

            if ($profile->user_id !== $user->id) {
                $profile->forceFill(['user_id' => $user->id])->saveQuietly();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
