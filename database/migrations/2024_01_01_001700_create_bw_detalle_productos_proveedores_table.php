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

        Schema::create('bw_detalle_productos_proveedores', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_detalle', true, false);
            $table->integer('cod_proveedor');
            $table->integer('cod_inventario');
            $table->tinyInteger('flag_tipo_inventario')->default('1');
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('cod_inventario', 'fk_bw_detalle_prod_prove_bw_inv_quimicos_idx');
            $table->index('cod_proveedor', 'fk_bw_detalle_prod_prove_bw_proveedores_idx');
            $table->index('user_insert', 'fk_bw_detalle_prod_prove_usu_usuarios_idx');

            $table->foreign('cod_proveedor', 'fk_bw_detalle_prod_prove_bw_proveedores')->references('cod_proveedor')->on('bw_proveedores');
            $table->foreign('user_insert', 'fk_bw_detalle_prod_prove_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bw_detalle_productos_proveedores');
        Schema::enableForeignKeyConstraints();
    }
};
