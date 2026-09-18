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

        Schema::create('bw_plantaciones_semillas_bloques', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_detalle_bloque', true, false);
            $table->integer('cod_detalle');
            $table->integer('cod_bloque');
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('cod_detalle', 'fk_bw_plant_sem_blo_w_plant_sem_idx');
            $table->index('cod_bloque', 'fk_bw_plant_sem_bw_bloques_idx');
            $table->index('user_insert', 'fk_bw_plant_sem_blo_usu_usuarios_idx');

            $table->foreign('user_insert', 'fk_bw_plant_sem_blo_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
            $table->foreign('cod_detalle', 'fk_bw_plant_sem_blo_w_plant_sem')->references('cod_detalle')->on('bw_plantaciones_semillas');
            $table->foreign('cod_bloque', 'fk_bw_plant_sem_bw_bloques')->references('cod_bloque')->on('bw_bloques');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bw_plantaciones_semillas_bloques');
        Schema::enableForeignKeyConstraints();
    }
};
