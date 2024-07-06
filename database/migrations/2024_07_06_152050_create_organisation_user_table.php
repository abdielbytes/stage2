<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrganisationUserTable extends Migration
{
    public function up()
    {
        Schema::create('organisation_user', function (Blueprint $table) {
            $table->uuid('orgId');
            $table->uuid('userId');
            $table->timestamps();

            $table->foreign('orgId')->references('orgId')->on('organisations')->onDelete('cascade');
            $table->foreign('userId')->references('userId')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('organisation_user');
    }
}
