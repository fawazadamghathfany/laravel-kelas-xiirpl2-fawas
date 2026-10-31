<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // add foreign keys on table users
        Schema::table('users', function (Blueprint $table) {
            //
            $table->foreignId('profile_id')->constrained();
            $table->foreignId('role_id')->constrained();
        });
        // add foreign keys on table film
        Schema::table('films', function (Blueprint $table) {
            $table->foreignId('genre_id')->constrained();
        });
        // add foreign keys on table perans
        Schema::table('perans', function (Blueprint $table) {
            $table->foreignId('cast_id')->constrained();
            $table->foreignId('film_id')->constrained();
        });
        // add foreign keys on table kritiks
        Schema::table('kritiks', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained();
            $table->foreignId('film_id')->constrained();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // drop foreign on table users
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['profile_id']);
            $table->dropForeign(['role_id']);
            $table->dropColumn('profile_id');
            $table->dropColumn('role_id');
        });
        // drop foreign on table films
        Schema::table('films', function (Blueprint $table) {
            $table->dropForeign(['genre_id']);
            $table->dropColumn('genre_id');
        });
        // drop foreign keys on table perans
        Schema::table('perans', function (Blueprint $table) {
            $table->dropForeign(['film_id']);
            $table->dropForeign(['cast_id']);
            $table->dropColumn('cast_id');
            $table->dropColumn('film_id');
        });
        // add foreign keys on table kritiks
        Schema::table('kritiks', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['film_id']);
            $table->dropColumn('user_id');
            $table->dropColumn('film_id');
        });
    }
};
