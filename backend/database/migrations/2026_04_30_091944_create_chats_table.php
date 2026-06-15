<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**a
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chats', function (Blueprint $table) {
    $table->id();

    $table->unsignedBigInteger('sender_id');
    $table->unsignedBigInteger('receiver_id');

    $table->text('message')->nullable();

    // 🔥 tambahan penting
    $table->string('type')->default('text'); // text | image | product
    $table->boolean('is_read')->default(false);

    $table->timestamps();

    // 🔗 optional (kalau ada table users)
    $table->foreign('sender_id')->references('id')->on('users')->cascadeOnDelete();
    $table->foreign('receiver_id')->references('id')->on('users')->cascadeOnDelete();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};
