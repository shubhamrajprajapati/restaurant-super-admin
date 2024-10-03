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

            $table->string('host');
            $table->string('username');
            $table->string('password')->nullable(); // Optional, if using key-based authentication
            $table->string('private_key')->nullable(); // For private key authentication
            $table->integer('port')->default(22); // Default SSH port
            $table->boolean('active')->default(false); // To activate/deactivate SSH details

            $table->string('name')->default('public');
            $table->string('default_cmd')->nullable();
            $table->string('is_valid')->default(false);

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
