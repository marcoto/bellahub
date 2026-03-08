<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'worker'])->default('worker')->after('name');
            $table->string('phone')->nullable()->after('email');
            $table->string('color', 7)->default('#8b5cf6')->after('phone');
            $table->date('hire_date')->nullable()->after('color');
            $table->integer('vacation_days')->default(22)->after('hire_date');
            $table->boolean('is_active')->default(true)->after('vacation_days');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'phone', 'color', 'hire_date', 'vacation_days', 'is_active']);
        });
    }
};
