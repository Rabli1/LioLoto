<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const RENAMES = [
        'nom' => 'name',
        'motDePasse' => 'password',
        'solde' => 'points',
        'imageProfil' => 'profileImage',
        'couleurProfil' => 'profileColor',
        'or' => 'gold',
        'argent' => 'silver',
        'pointPerdu' => 'pointsLost',
        'confirme' => 'confirmed',
        'banni' => 'banned',
    ];

    public function up(): void
    {
        $usernameColumn = Schema::hasColumn('users', 'nom') ? 'nom' : 'name';
        if (DB::table('users')->select($usernameColumn)->groupBy($usernameColumn)->havingRaw('COUNT(*) > 1')->exists()) {
            throw new RuntimeException('Resolve duplicate usernames before migrating the users table.');
        }

        // Keep the original migration intact so installed databases can upgrade.
        foreach (self::RENAMES as $old => $new) {
            if (Schema::hasColumn('users', $old)) {
                if (Schema::hasColumn('users', $new)) {
                    throw new RuntimeException('Both legacy and current user columns exist: '.$old.'/'.$new.'. Reconcile them before migrating.');
                }
                Schema::table('users', fn (Blueprint $table) => $table->renameColumn($old, $new));
            }
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unique('name');
            $table->string('email')->nullable()->unique();
            $table->boolean('admin')->default(false);
            $table->unsignedInteger('lvl')->default(1);
            $table->unsignedInteger('exp')->default(0);
            $table->date('last_update')->nullable();
            $table->boolean('daily')->default(true);
            $table->string('confirmation_token', 64)->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['name']);
            $table->dropUnique(['email']);
            $table->dropColumn(['email', 'admin', 'lvl', 'exp', 'last_update', 'daily', 'confirmation_token', 'remember_token', 'created_at', 'updated_at']);
        });

        foreach (self::RENAMES as $old => $new) {
            Schema::table('users', fn (Blueprint $table) => $table->renameColumn($new, $old));
        }
    }
};
