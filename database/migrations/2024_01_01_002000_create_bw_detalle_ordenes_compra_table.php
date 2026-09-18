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

        Schema::create('bw_detalle_ordenes_compra', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_detalle', true, false);
            $table->integer('cod_orden');
            $table->integer('cod_detalle_producto');
            $table->decimal('cantidad', 11, 3)->default('1.000');
            $table->integer('cod_unidad_medida');
            $table->integer('cod_tipo_semilla')->default('1');
            $table->decimal('precio_semilla', 12, 5)->default('0.00000');
            $table->decimal('monto_pago', 12, 5)->default('0.00000');
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('cod_orden', 'fk_bw_det_orden_compra_bw_orden_compra_idx');
            $table->index('cod_detalle_producto', 'fk_bw:_det_orden_compra_bw_det_prod_provedores_idx');
            $table->index('user_insert', 'fk_bw_det_orden_compra_ug_usuarios_idx');
            $table->index('cod_unidad_medida', 'fk_bw_det_orde_compra_ug_unidades_medida_idx');

            $table->foreign('cod_detalle_producto', 'fk_bw_det_orden_compra_bw_det_prod_provedores')->references('cod_detalle')->on('bw_detalle_productos_proveedores');
            $table->foreign('cod_orden', 'fk_bw_det_orden_compra_bw_orden_compra')->references('cod_orden')->on('bw_ordenes_compra');
            $table->foreign('cod_unidad_medida', 'fk_bw_det_orden_compra_ug_unidades_medida')->references('cod_unidad_medida')->on('ug_unidades_medida');
            $table->foreign('user_insert', 'fk_bw_det_orden_compra_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bw_detalle_ordenes_compra');
        Schema::enableForeignKeyConstraints();
    }
};
