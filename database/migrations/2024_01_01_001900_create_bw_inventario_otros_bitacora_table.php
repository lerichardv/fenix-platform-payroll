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

        Schema::create('bw_inventario_otros_bitacora', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_bitacora', true, false);
            $table->integer('cod_inventario');
            $table->integer('cod_info_empresa');
            $table->string('nombre_producto', 100);
            $table->integer('cantidad_producto')->default('1');
            $table->integer('codigo_producto');
            $table->integer('cod_unidad_medida');
            $table->string('orden_compra', 15)->nullable()->default(null);
            $table->date('fecha_inventario')->nullable()->default(null);
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->date('date_insert');
            $table->integer('user_update');
            $table->timestamp('date_update')->useCurrent();

            $table->index('cod_inventario', 'fk_bw_inventario_otros_bitacora_bw_inventario_otros_idx');
            $table->index('user_update', 'fk_bw_inventario_otros_bitacora_usu_usuarios_idx');

            $table->foreign('cod_inventario', 'fk_bw_inventario_otros_bitacora_bw_inventario_otros')->references('cod_inventario')->on('bw_inventario_otros');
            $table->foreign('user_update', 'fk_bw_inventario_otros_bitacora_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bw_inventario_otros_bitacora');
        Schema::enableForeignKeyConstraints();
    }
};
