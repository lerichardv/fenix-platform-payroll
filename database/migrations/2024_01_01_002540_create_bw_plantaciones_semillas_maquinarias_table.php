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

        Schema::create('bw_plantaciones_semillas_maquinarias', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_detalle_maquinaria', true, false);
            $table->integer('cod_detalle');
            $table->integer('cod_maquinaria');
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('cod_detalle', 'fk_bw_plant_sem_maq_bw_plant_sem_idx');
            $table->index('cod_maquinaria', 'fk_bw_plant_sem_maq_bw_inv_maq_idx');
            $table->index('user_insert', 'fk_bw_plant_sem_maq_usu_usuarios_idx');

            $table->foreign('cod_maquinaria', 'fk_bw_plant_sem_maq_bw_inv_maq')->references('cod_inventario')->on('bw_inventario_maquinaria');
            $table->foreign('cod_detalle', 'fk_bw_plant_sem_maq_bw_plant_sem')->references('cod_detalle')->on('bw_plantaciones_semillas');
            $table->foreign('user_insert', 'fk_bw_plant_sem_maq_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bw_plantaciones_semillas_maquinarias');
        Schema::enableForeignKeyConstraints();
    }
};
