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
        Schema::create('menu_option_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained()->onDelete('cascade');
            $table->string('label'); // contoh: Level Gula, Topping
            $table->string('input_type'); // radio, checkbox, select, switch, number, text, textarea
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('min_select')->default(0);
            $table->unsignedInteger('max_select')->nullable();
            $table->string('placeholder')->nullable();
            $table->integer('min_value')->nullable();
            $table->integer('max_value')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_option_groups');
    }
};
