<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('general_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        $defaults = [
            'company_name' => 'PT Indikarya',
            'company_logo' => null,
            'company_address' => 'Jl. Example No. 123, Jakarta',
            'company_phone' => '021-1234567',
            'company_email' => 'info@indikarya.com',
        ];

        foreach ($defaults as $key => $value) {
            DB::table('general_settings')->insert([
                'key' => $key,
                'value' => $value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('general_settings');
    }
};
