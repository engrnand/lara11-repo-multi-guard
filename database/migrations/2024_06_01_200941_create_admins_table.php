<?php

use App\Enum\GenderEnum;
use App\Enum\NotificationTypeEnum;
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
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique()->index();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('avatar')->nullable();
            $table->enum('gender', array_column(GenderEnum::cases(), 'value'))->nullable();
            $table->date('dob')->nullable();
            $table->string('phone', 20)->nullable();
            $table->enum('two_factor', array_column(NotificationTypeEnum::cases(), 'value'))->nullable();
            $table->enum('notification', array_column(NotificationTypeEnum::cases(), 'value'))->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
