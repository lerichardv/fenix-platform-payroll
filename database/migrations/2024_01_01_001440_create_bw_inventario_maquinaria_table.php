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

        Schema::create('bw_inventario_maquinaria', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_inventario', true, false);
            $table->integer('cod_info_empresa');
            $table->string('codigo_maquinaria', 45);
            $table->string('nombre_maquinaria', 100);
            $table->integer('cod_tipo_aplicacion');
            $table->integer('cantidad_maquinaria')->default('1');
            $table->decimal('precio_unidad', 11, 3)->nullable()->default(null);
            $table->integer('anio_vencimiento')->nullable()->default(null);
            $table->integer('cod_estado_plantacion')->nullable()->default(null);
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('user_insert', 'fk_bw_inventario_maq_usu_usuarios_idx');
            $table->index('cod_tipo_aplicacion', 'fk_bw_inventario_maq_bw_tipo_aplicacion_maq_idx');
            $table->index('cod_info_empresa', 'fk_bw_inventario_maquinaria_bw_info_empresa_idx');
            $table->index('cod_estado_plantacion', 'fk_bw_inventario_maquinaria_bw_estados_plantacion_idx');

            $table->foreign('cod_tipo_aplicacion', 'fk_bw_inventario_maq_bw_tipo_aplicacion_maq')->references('cod_tipo_aplicacion')->on('bw_tipo_aplicacion_maquinaria');
            $table->foreign('user_insert', 'fk_bw_inventario_maq_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
            $table->foreign('cod_estado_plantacion', 'fk_bw_inventario_maquinaria_bw_estados_plantacion')->references('cod_estado_plantacion')->on('bw_estados_plantacion');
            $table->foreign('cod_info_empresa', 'fk_bw_inventario_maquinaria_bw_info_empresa')->references('cod_info_empresa')->on('bw_info_empresa');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bw_inventario_maquinaria');
        Schema::enableForeignKeyConstraints();
    }
};
