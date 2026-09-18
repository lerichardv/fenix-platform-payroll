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

        Schema::create('bw_plantaciones_semillas', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_detalle', true, false);
            $table->integer('cod_plantacion');
            $table->integer('cod_inventario_semilla');
            $table->decimal('cantidad_usada', 11, 3);
            $table->text('descripcion')->nullable();
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('cod_plantacion', 'fk_bw_plantaciones_semillas_bw_plantaciones_idx');
            $table->index('cod_inventario_semilla', 'fk_bw_plantaciones_semillas_bw_inventario_semillas_idx');
            $table->index('user_insert', 'fk_bw_plantaciones_semillas_usu_usuarios_idx');

            $table->foreign('cod_inventario_semilla', 'fk_bw_plantaciones_semillas_bw_inventario_semillas')->references('cod_inventario')->on('bw_inventario_semilla');
            $table->foreign('cod_plantacion', 'fk_bw_plantaciones_semillas_bw_plantaciones')->references('cod_plantacion')->on('bw_plantaciones');
            $table->foreign('user_insert', 'fk_bw_plantaciones_semillas_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bw_plantaciones_semillas');
        Schema::enableForeignKeyConstraints();
    }
};
