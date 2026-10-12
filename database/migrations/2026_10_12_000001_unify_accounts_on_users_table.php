<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * One account per person. `users` becomes the only credential store; support_teams
 * and solve_users turn into module profiles pointing at it through `user_id`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('email');
        });

        Schema::table('support_teams', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->unique()->after('id');
        });

        Schema::connection('mysql_solves')->table('solve_users', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->unique()->after('id');
        });

        // Backfill: every existing profile gets a users row that keeps its current password hash.
        foreach (DB::table('support_teams')->get() as $member) {
            $userId = $this->userFor(
                email: $member->email,
                name: trim($member->first_name . ' ' . $member->last_name),
                username: null,
                password: $member->password,
            );
            DB::table('support_teams')->where('id', $member->id)->update(['user_id' => $userId]);
        }

        foreach (DB::connection('mysql_solves')->table('solve_users')->get() as $member) {
            $userId = $this->userFor(
                email: $member->username . '@solves.local',
                name: trim(($member->first_name ?? '') . ' ' . ($member->last_name ?? '')) ?: $member->username,
                username: $member->username,
                password: $member->password,
            );
            DB::connection('mysql_solves')->table('solve_users')->where('id', $member->id)->update(['user_id' => $userId]);
        }
    }

    public function down(): void
    {
        Schema::connection('mysql_solves')->table('solve_users', fn (Blueprint $t) => $t->dropColumn('user_id'));
        Schema::table('support_teams', fn (Blueprint $t) => $t->dropColumn('user_id'));
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn('username'));
    }

    private function userFor(string $email, string $name, ?string $username, string $password): int
    {
        $existing = DB::table('users')->where('email', $email)->first();

        if ($existing) {
            if ($username && ! $existing->username) {
                DB::table('users')->where('id', $existing->id)->update(['username' => $username]);
            }

            return $existing->id;
        }

        return DB::table('users')->insertGetId([
            'name' => $name,
            'email' => $email,
            'username' => $username,
            'password' => $password,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
