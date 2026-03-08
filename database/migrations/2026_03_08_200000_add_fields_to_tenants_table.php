<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');
            $table->string('email')->nullable()->after('name');
            $table->string('phone')->nullable()->after('email');
            $table->string('city')->nullable()->after('phone');
            $table->string('address')->nullable()->after('city');
            $table->string('logo')->nullable()->after('address');
            $table->string('primary_color', 7)->default('#8b5cf6')->after('logo');
            $table->string('secondary_color', 7)->default('#6d28d9')->after('primary_color');
            $table->enum('plan', ['basic', 'professional', 'enterprise'])->default('basic')->after('secondary_color');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('plan');
            $table->string('admin_name')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'name', 'email', 'phone', 'city', 'address', 'logo',
                'primary_color', 'secondary_color', 'plan', 'status', 'admin_name',
            ]);
        });
    }
};
