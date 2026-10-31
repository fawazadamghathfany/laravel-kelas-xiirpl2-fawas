<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('user');
        });

        if (Schema::hasTable('roles') && Schema::hasColumn('users', 'role_id')) {
            $adminRoleId = DB::table('roles')->where('name', 'admin')->value('id');

            if ($adminRoleId !== null) {
                DB::table('users')->where('role_id', $adminRoleId)->update(['role' => 'admin']);
            }
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
