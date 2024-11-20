<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('markers', function (Blueprint $table) {
        $table->string('photo_path')->nullable(); // Menambahkan kolom untuk path foto
    });
}

    /**
     * Reverse the migrations.
     */
    public function down()
{
    Schema::table('markers', function (Blueprint $table) {
        $table->dropColumn('photo_path'); // Menghapus kolom saat rollback
    });
}
};
