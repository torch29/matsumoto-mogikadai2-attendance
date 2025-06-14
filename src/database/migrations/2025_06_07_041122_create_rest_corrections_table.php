<?php

use Doctrine\DBAL\Schema\SchemaManagerFactory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRestCorrectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rest_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_correction_id')->constrained()->cascadeOnDelete();
            $table->time('corrected_rest_start');
            $table->time('corrected_rest_end');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rest_corrections', function (Blueprint $table) {
            $table->dropForeign(['rest_id']);
        });

        Schema::dropIfExists('rest_corrections');
    }
}
