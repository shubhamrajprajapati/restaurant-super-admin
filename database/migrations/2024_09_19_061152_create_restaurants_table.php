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
        Schema::create('restaurants', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('name');
            $table->mediumText('description');
            $table->string('domain')->unique();
            $table->string('logo')->nullable();

            $table->string('installation_token')->unique();

            $table->boolean('featured')->default(false);
            $table->boolean('visible')->default(false);
            $table->boolean('verified')->default(false);

            $table->boolean('status')->default(false);
            $table->string('status_msg')->nullable();
            $table->boolean('online_order_status')->default(false);
            $table->string('online_order_msg')->nullable();
            $table->boolean('reservation_status')->default(false);
            $table->string('reservation_msg')->nullable();
            $table->boolean('shutdown_status')->default(false);
            $table->string('shutdown_msg')->nullable();

            $table->unsignedSmallInteger('order_column')->nullable();

            $table->json('other_details')->nullable();

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
        Schema::dropIfExists('restaurants');
    }
};
