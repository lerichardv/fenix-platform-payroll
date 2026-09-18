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

        Schema::create('bw_conversiones_unidades_medida', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_conversion', true, false);
            $table->integer('cod_unidad_medida_origen');
            $table->integer('cod_unidad_medida_destino');
            $table->decimal('conversion', 15, 4)->default('1.0000');
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('cod_unidad_medida_origen', 'fk_bw_conversiones_ug_unidad_medida_origen_idx');
            $table->index('cod_unidad_medida_destino', 'fk_bw_conversiones_ug_unidad_medida_destino_idx');
            $table->index('user_insert', 'fk_bw_conversiones_usu_usuarios_idx');

            $table->foreign('cod_unidad_medida_destino', 'fk_bw_conversiones_ug_unidad_medida_destino')->references('cod_unidad_medida')->on('ug_unidades_medida');
            $table->foreign('cod_unidad_medida_origen', 'fk_bw_conversiones_ug_unidad_medida_origen')->references('cod_unidad_medida')->on('ug_unidades_medida');
            $table->foreign('user_insert', 'fk_bw_conversiones_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bw_conversiones_unidades_medida');
        Schema::enableForeignKeyConstraints();
    }
};
