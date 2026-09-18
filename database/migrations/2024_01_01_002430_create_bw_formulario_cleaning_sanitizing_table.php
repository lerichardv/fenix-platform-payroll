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

        Schema::create('bw_formulario_cleaning_sanitizing', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_formulario', true, false);
            $table->integer('cod_detalle')->nullable()->default(null);
            $table->integer('cod_plantacion')->nullable()->default(null);
            $table->date('fecha_limpieza');
            $table->time('vez_limpieza');
            $table->string('equipo_limpieza', 45);
            $table->tinyInteger('cleaning_tools');
            $table->tinyInteger('cleaning_potable_water');
            $table->tinyInteger('cleaning_detergent');
            $table->tinyInteger('scrubbing');
            $table->tinyInteger('rinse_potable_water');
            $table->tinyInteger('sanitizing_chlorine');
            $table->tinyInteger('post_sanitizing');
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('user_insert', 'fk_formulario_cl_san_usu_usuarios_idx');
            $table->index('cod_detalle', 'fk_formulario_cle_san_bw_det_blo_pla_idx');
            $table->index('cod_plantacion', 'fk_formulario_cle_san_bw_plantaciones_idx');

            $table->foreign('user_insert', 'fk_formulario_cl_san_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
            $table->foreign('cod_detalle', 'fk_formulario_cle_san_bw_det_blo_pla')->references('cod_detalle')->on('bw_detalle_bloques_plantaciones');
            $table->foreign('cod_plantacion', 'fk_formulario_cle_san_bw_plantaciones')->references('cod_plantacion')->on('bw_plantaciones');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bw_formulario_cleaning_sanitizing');
        Schema::enableForeignKeyConstraints();
    }
};
