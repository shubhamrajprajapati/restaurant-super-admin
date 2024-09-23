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
        Schema::create('restaurant_s_s_h_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('restaurant_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();

            $table->string('ssh_host');
            $table->string('ssh_username');
            $table->string('ssh_password')->nullable(); // Optional, if using key-based authentication
            $table->string('ssh_private_key')->nullable(); // For private key authentication
            $table->integer('ssh_port')->default(22); // Default SSH port
            $table->boolean('ssh_active')->default(true); // To activate/deactivate SSH details

            $table->unsignedSmallInteger('order_column')->nullable();

            $table->foreignUuid('updated_by_user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignUuid('created_by_user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();

            $table->softDeletes();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurant_s_s_h_details');
    }
};
