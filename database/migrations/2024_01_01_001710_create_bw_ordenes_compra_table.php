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

        Schema::create('bw_ordenes_compra', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_orden', true, false);
            $table->integer('cod_info_empresa');
            $table->integer('cod_proveedor');
            $table->date('fecha_orden');
            $table->date('fecha_estimada_entrega')->nullable()->default(null);
            $table->date('fecha_recibido_pedido')->nullable()->default(null);
            $table->text('observaciones')->nullable();
            $table->text('adjunto_orden_compra')->nullable();
            $table->string('num_orden_compra', 10)->nullable()->default(null);
            $table->integer('cod_estado_orden_compra')->default('1');
            $table->tinyInteger('completada')->default('0');
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('user_insert', 'fk_bw_orden_compra_usu_usuarios_idx');
            $table->index('cod_info_empresa', 'fk_bw_orden_compra_usu_gerencias_idx');
            $table->index('cod_proveedor', 'fk_bw_ordenes_compra_bw_proveedores_idx');
            $table->index('cod_estado_orden_compra', 'fk_bw_ordens_compra_ug_estados_ordenes_compra_idx');

            $table->foreign('cod_info_empresa', 'fk_bw_orden_compra_bw_info_empresas')->references('cod_info_empresa')->on('bw_info_empresa');
            $table->foreign('user_insert', 'fk_bw_orden_compra_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
            $table->foreign('cod_proveedor', 'fk_bw_ordenes_compra_bw_proveedores')->references('cod_proveedor')->on('bw_proveedores');
            $table->foreign('cod_estado_orden_compra', 'fk_bw_ordens_compra_ug_estados_ordenes_compra')->references('cod_estado_orden_compra')->on('ug_estados_ordenes_compra');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bw_ordenes_compra');
        Schema::enableForeignKeyConstraints();
    }
};
