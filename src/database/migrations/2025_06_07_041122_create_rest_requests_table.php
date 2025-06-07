<?php

use Doctrine\DBAL\Schema\SchemaManagerFactory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRestRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rest_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rest_id')->constrained()->cascadeOnDelete();
            $table->time('corrected_rest_time');
            $table->time('corrected_return_from_rest');
            $table->string('content');
            $table->string('approve_status')->default('pending');
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
        Schema::table('rest_requests', function (Blueprint $table) {
            $table->dropForeign(['rest_id']);
        });

        Schema::dropIfExists('rest_requests');
    }
}
