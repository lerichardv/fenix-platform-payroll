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

        Schema::create('far_bloques', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_bloque', true, false);
            $table->integer('cod_farm');
            $table->integer('cod_field');
            $table->string('bloque', 145);
            $table->decimal('acres', 3, 2)->default('0.00');
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('user_insert', 'fk_far_bloques_usu_usuarios1_idx');
            $table->index('cod_field', 'fk_far_bloques_far_fields1_idx');
            $table->index('cod_farm', 'fk_far_bloques_far_farms_idx');

            $table->foreign('cod_farm', 'fk_far_bloques_far_farms')->references('cod_farms')->on('far_farms');
            $table->foreign('cod_field', 'fk_far_bloques_far_fields1')->references('cod_field')->on('far_fields');
            $table->foreign('user_insert', 'fk_far_bloques_usu_usuarios1')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('far_bloques');
        Schema::enableForeignKeyConstraints();
    }
};
