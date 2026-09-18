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

        Schema::create('bw_plantaciones', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_plantacion')->primary();
            $table->integer('cod_info_empresa');
            $table->integer('anio_plantacion');
            $table->string('num_plantacion', 11);
            $table->date('fecha_plantacion_planeada')->nullable()->default(null);
            $table->date('fecha_plantacion_ejecutada')->nullable()->default(null);
            $table->decimal('acres_plantados', 7, 3);
            $table->integer('cod_usuario_planificacion')->nullable()->default(null);
            $table->integer('cod_usuario_plantacion')->nullable()->default(null);
            $table->integer('cod_estado')->default('1');
            $table->integer('cod_temporada')->nullable()->default(null);
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('user_insert', 'fk_bw_plantaciones_usu_usuarios_idx');
            $table->index('cod_usuario_plantacion', 'fk_bw_plantaciones_usu_usuarios_plantacion_idx');
            $table->index('cod_usuario_planificacion', 'fk_bw_plantaciones_usu_usuarios_planificacion_idx');
            $table->index('cod_info_empresa', 'fk_bw_plantaciones_bw_info_empresa_idx');
            $table->index('cod_estado', 'fk_bw_plantaciones_bw_estados_plantacion_idx');
            $table->index('cod_temporada', 'fk_bw_plantaciones_bw_temporadas_idx');

            $table->foreign('cod_estado', 'fk_bw_plantaciones_bw_estados_plantacion')->references('cod_estado_plantacion')->on('bw_estados_plantacion');
            $table->foreign('cod_info_empresa', 'fk_bw_plantaciones_bw_info_empresa')->references('cod_info_empresa')->on('bw_info_empresa');
            $table->foreign('cod_temporada', 'fk_bw_plantaciones_bw_temporadas')->references('cod_temporada')->on('bw_temporadas');
            $table->foreign('user_insert', 'fk_bw_plantaciones_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
            $table->foreign('cod_usuario_planificacion', 'fk_bw_plantaciones_usu_usuarios_planificacion')->references('cod_usuario')->on('usu_usuarios');
            $table->foreign('cod_usuario_plantacion', 'fk_bw_plantaciones_usu_usuarios_plantacion')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bw_plantaciones');
        Schema::enableForeignKeyConstraints();
    }
};
