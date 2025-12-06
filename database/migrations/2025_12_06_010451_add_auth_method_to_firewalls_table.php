<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('firewalls', function (Blueprint $table) {
            $table->string('auth_method')->default('basic')->after('url'); // 'basic' or 'token'
            $table->text('api_token')->nullable()->after('api_secret');
            // Make api_key and api_secret nullable since token auth doesn't need them
            $table->string('api_key')->nullable()->change();
            $table->string('api_secret')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('firewalls', function (Blueprint $table) {
            //
        });
    }
};
