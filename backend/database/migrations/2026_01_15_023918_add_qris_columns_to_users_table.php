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
        if (!Schema::hasColumn('users', 'qris_image') || !Schema::hasColumn('users', 'qris_name')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'qris_image')) {
                    $table->string('qris_image')->nullable()->after('email');
                }
                if (!Schema::hasColumn('users', 'qris_name')) {
                    $table->string('qris_name')->nullable()->after('qris_image');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'qris_image') || Schema::hasColumn('users', 'qris_name')) {
            Schema::table('users', function (Blueprint $table) {
                $columns = [];
                if (Schema::hasColumn('users', 'qris_image')) {
                    $columns[] = 'qris_image';
                }
                if (Schema::hasColumn('users', 'qris_name')) {
                    $columns[] = 'qris_name';
                }
                if (!empty($columns)) {
                    $table->dropColumn($columns);
                }
            });
        }
    }
};
