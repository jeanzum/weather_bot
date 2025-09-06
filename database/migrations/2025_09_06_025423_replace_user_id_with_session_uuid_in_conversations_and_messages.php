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
        Schema::table('conversations', function (Blueprint $table) {
            $table->uuid('session_uuid')->after('id');
            $table->index('session_uuid');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->uuid('session_uuid')->after('conversation_id');
            $table->index('session_uuid');
        });

        // Remove user_id columns after adding session_uuid
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->dropIndex(['session_uuid']);
            $table->dropColumn('session_uuid');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['session_uuid']);
            $table->dropColumn('session_uuid');
        });
    }
};
