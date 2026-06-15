<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {

            // relasi user (kalau belum ada)
            if (!Schema::hasColumn('notifications', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id');
            }

            // isi pesan
            if (!Schema::hasColumn('notifications', 'title')) {
                $table->string('title')->nullable();
            }

            if (!Schema::hasColumn('notifications', 'message')) {
                $table->text('message')->nullable();
            }

            // status baca
            if (!Schema::hasColumn('notifications', 'read_at')) {
                $table->timestamp('read_at')->nullable();
            }

            // status tipe notif (process / done / new)
            if (!Schema::hasColumn('notifications', 'type')) {
                $table->string('type')->default('new');
            }

            // estimasi (untuk user)
            if (!Schema::hasColumn('notifications', 'estimation_minutes')) {
                $table->integer('estimation_minutes')->nullable();
            }

            // relasi order
            if (!Schema::hasColumn('notifications', 'order_id')) {
                $table->unsignedBigInteger('order_id')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn([
                'user_id',
                'title',
                'message',
                'read_at',
                'type',
                'estimation_minutes',
                'order_id'
            ]);
        });
    }
};