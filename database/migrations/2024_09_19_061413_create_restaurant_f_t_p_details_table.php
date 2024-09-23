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
        Schema::create('restaurant_f_t_p_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('restaurant_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();

            $table->string('ftp_server');
            $table->string('ftp_username');
            $table->string('ftp_password');
            $table->integer('ftp_port')->default(21); // Default FTP port
            $table->string('ftp_directory')->nullable(); // Optional directory
            $table->boolean('ftp_active')->default(true); // To activate/deactivate FTP details

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
        Schema::dropIfExists('restaurant_f_t_p_details');
    }
};
