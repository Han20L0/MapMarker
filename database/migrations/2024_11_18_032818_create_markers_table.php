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
    Schema::create('markers', function (Blueprint $table) {
        $table->id();
        $table->enum('quarantine', ['Karantina Hewan', 'Karantina Ikan', 'Karantina Tumbuhan']);
        $table->string('commodity');
        $table->string('disease');
        $table->string('information');
        $table->enum('color', ['red', 'green']);
        $table->date('date_found');
        $table->decimal('latitude', 10, 8);
        $table->decimal('longitude', 11, 8);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('markers');
    }
};
