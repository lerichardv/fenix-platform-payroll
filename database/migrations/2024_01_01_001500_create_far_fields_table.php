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
        Schema::disableForeignKeyConstraints();

        Schema::create('far_fields', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_field')->autoIncrement();
            $table->integer('cod_farm');
            $table->string('field', 45);
            $table->string('activo', 45)->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->primary(['cod_field', 'cod_farm']);

            $table->index('cod_farm', 'fk_far_fields_far_growers1_idx');
            $table->index('user_insert', 'fk_far_fields_usu_usuarios1_idx');

            $table->foreign('cod_farm', 'fk_far_fields_far_farms1')->references('cod_farms')->on('far_farms');
            $table->foreign('user_insert', 'fk_far_fields_usu_usuarios1')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('far_fields');
        Schema::enableForeignKeyConstraints();
    }
};
