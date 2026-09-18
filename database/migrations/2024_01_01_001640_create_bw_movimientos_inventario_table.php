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

        Schema::create('bw_movimientos_inventario', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_movimiento', true, false);
            $table->integer('cod_info_empresa_envia');
            $table->integer('cod_info_empresa_recibe');
            $table->integer('cod_inventario');
            $table->integer('cod_tipo_inventario')->default('1');
            $table->decimal('cantidad_enviada', 11, 3);
            $table->decimal('cantidad_recibida', 11, 3)->nullable()->default(null);
            $table->integer('cod_unidad_medida')->nullable()->default(null);
            $table->integer('num_lote')->nullable()->default(null);
            $table->text('motivo_perdida')->nullable();
            $table->date('fecha_envia')->nullable()->default(null);
            $table->date('fecha_recibe')->nullable()->default(null);
            $table->integer('user_envia');
            $table->integer('user_recibe')->nullable()->default(null);
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('cod_info_empresa_envia', 'fk_bw_movimentos_inventario_bw_info_empresa_envai_idx');
            $table->index('cod_info_empresa_recibe', 'fk_bw_movimentos_inventario_bw_info_empresa_recibe_idx');
            $table->index('user_envia', 'fk_bw_movimientos_inventario_usu_usuarios_idx');
            $table->index('user_recibe', 'fk_bw_movimientos_inventario_usu_usuarios_recibe_idx');
            $table->index('user_insert', 'fk_bw_movimientos_inventario_usu_usuarios_idx1');
            $table->index('cod_unidad_medida', 'fk_bw_movimientos_ug_unidades_medida_idx');

            $table->foreign('cod_info_empresa_envia', 'fk_bw_movimentos_inventario_bw_info_empresa_envia')->references('cod_info_empresa')->on('bw_info_empresa');
            $table->foreign('cod_info_empresa_recibe', 'fk_bw_movimentos_inventario_bw_info_empresa_recibe')->references('cod_info_empresa')->on('bw_info_empresa');
            $table->foreign('user_insert', 'fk_bw_movimientos_inventario_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
            $table->foreign('user_envia', 'fk_bw_movimientos_inventario_usu_usuarios_envia')->references('cod_usuario')->on('usu_usuarios');
            $table->foreign('user_recibe', 'fk_bw_movimientos_inventario_usu_usuarios_recibe')->references('cod_usuario')->on('usu_usuarios');
            $table->foreign('cod_unidad_medida', 'fk_bw_movimientos_ug_unidades_medida')->references('cod_unidad_medida')->on('ug_unidades_medida');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bw_movimientos_inventario');
        Schema::enableForeignKeyConstraints();
    }
};
