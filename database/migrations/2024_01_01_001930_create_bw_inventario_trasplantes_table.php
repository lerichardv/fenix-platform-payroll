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

        Schema::create('bw_inventario_trasplantes', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_trasplante', true, false);
            $table->integer('cod_inventario');
            $table->integer('cod_plantacion');
            $table->integer('cod_localizacion');
            $table->integer('cod_info_empresa');
            $table->string('numero_orden', 68)->nullable()->default(null);
            $table->timestamp('fecha_entrega')->nullable()->default(null);
            $table->timestamp('fecha_recibo')->nullable()->useCurrent();
            $table->string('numero_ticket', 100)->nullable()->default(null);
            $table->string('tray', 145)->nullable()->default(null);
            $table->string('cantidad_plantas', 145)->nullable()->default(null);
            $table->string('germinacion', 45)->nullable()->default(null);
            $table->string('total_trays', 145)->nullable()->default(null);
            $table->string('total_plantas', 145)->nullable()->default(null);
            $table->tinyInteger('completado')->default('0');
            $table->tinyInteger('implementado')->default('0');
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->unique('cod_trasplante', 'cod_trasplante_UNIQUE');

            $table->index('cod_inventario', 'fk_cod_inventario_idx');
            $table->index('cod_plantacion', 'fk_cod_plantacion_idx');
            $table->index('cod_localizacion', 'fk_cod_localizacion_idx');
            $table->index('cod_info_empresa', 'fk_cod_info_empresa_idx');

            $table->foreign('cod_info_empresa', 'fk_cod_info_empresa')->references('cod_info_empresa')->on('bw_info_empresa');
            $table->foreign('cod_inventario', 'fk_cod_inventario')->references('cod_inventario')->on('bw_inventario_semilla');
            $table->foreign('cod_localizacion', 'fk_cod_localizacion')->references('cod_localizacion')->on('bw_inventario_localizaciones_trasplantes');
            $table->foreign('cod_plantacion', 'fk_cod_plantacion')->references('cod_plantacion')->on('bw_inventario_plantaciones');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bw_inventario_trasplantes');
        Schema::enableForeignKeyConstraints();
    }
};
