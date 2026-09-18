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

        Schema::create('bw_inventario_otros', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_inventario', true, false);
            $table->integer('cod_info_empresa');
            $table->string('nombre_producto', 100);
            $table->integer('cantidad_producto')->default('1');
            $table->integer('codigo_producto');
            $table->integer('cod_unidad_medida');
            $table->string('orden_compra', 15)->nullable()->default(null);
            $table->date('fecha_inventario')->nullable()->default(null);
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('cod_info_empresa', 'fk_bw_inventario_otros_bw_info_empresa_idx');
            $table->index('cod_unidad_medida', 'fk_bw_inventario_otros_ug_unidad_medida_idx');
            $table->index('user_insert', 'fk_bw_inventario_otros_usu_usuarios_idx');

            $table->foreign('cod_info_empresa', 'fk_bw_inventario_otros_bw_info_empresa')->references('cod_info_empresa')->on('bw_info_empresa');
            $table->foreign('cod_unidad_medida', 'fk_bw_inventario_otros_ug_unidad_medida')->references('cod_unidad_medida')->on('ug_unidades_medida');
            $table->foreign('user_insert', 'fk_bw_inventario_otros_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bw_inventario_otros');
        Schema::enableForeignKeyConstraints();
    }
};
