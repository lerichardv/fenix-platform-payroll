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

        Schema::create('bw_formularios', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_formulario')->primary();
            $table->string('nombre_formulario', 75);
            $table->string('descripcion_formulario', 200)->nullable()->default(null);
            $table->integer('cod_estado');
            $table->decimal('puntuacion_minima', 6, 3)->nullable()->default(null);
            $table->decimal('puntuacion_maxima', 6, 3)->nullable()->default(null);
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('user_insert', 'fk_bw_formularios_usu_usuarios_idx');
            $table->index('cod_estado', 'fk_bw_formularios_bw_estados_plantacion_idx');

            $table->foreign('cod_estado', 'fk_bw_formularios_bw_estados_plantacion')->references('cod_estado_plantacion')->on('bw_estados_plantacion');
            $table->foreign('user_insert', 'fk_bw_formularios_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bw_formularios');
        Schema::enableForeignKeyConstraints();
    }
};
