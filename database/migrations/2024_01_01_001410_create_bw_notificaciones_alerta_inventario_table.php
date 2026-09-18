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

        Schema::create('bw_notificaciones_alerta_inventario', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_notificacion', true, false);
            $table->integer('cod_info_empresa');
            $table->integer('cod_usuario');
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('cod_usuario', 'fk_bw_notificaciones_alerta_inventario_usu_usuarios1_idx');
            $table->index('user_insert', 'fk_bw_notificaciones_alerta_inventario_usu_usuarios2_idx');
            $table->index('cod_info_empresa', 'fk_bw_notificaciones_alerta_inventario_bw_info_empresa_idx');

            $table->foreign('cod_info_empresa', 'fk_bw_notificaciones_alerta_inventario_bw_info_empresa')->references('cod_info_empresa')->on('bw_info_empresa');
            $table->foreign('cod_usuario', 'fk_bw_notificaciones_alerta_inventario_usu_usuarios1')->references('cod_usuario')->on('usu_usuarios');
            $table->foreign('user_insert', 'fk_bw_notificaciones_alerta_inventario_usu_usuarios2')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bw_notificaciones_alerta_inventario');
        Schema::enableForeignKeyConstraints();
    }
};
