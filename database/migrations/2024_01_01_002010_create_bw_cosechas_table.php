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

        Schema::create('bw_cosechas', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_cosecha')->primary();
            $table->integer('cod_plantacion');
            $table->date('fecha_cosecha');
            $table->time('hora_inicio')->nullable()->default(null);
            $table->time('hora_final')->nullable()->default(null);
            $table->integer('cod_usuario_planificacion');
            $table->integer('cod_usuario_cosecha')->nullable()->default(null);
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('cod_plantacion', 'fk_bw_cosechas_bw_plantaciones_idx');
            $table->index('cod_usuario_planificacion', 'fk_bw_cosechas_usu_usuarios_planificacion_idx');
            $table->index('cod_usuario_cosecha', 'fk_bw_cosechas_usu_usuarios_cosecha_idx');
            $table->index('user_insert', 'fk_bw_cosechas_usu_usuarios');

            $table->foreign('cod_plantacion', 'fk_bw_cosechas_bw_plantaciones')->references('cod_plantacion')->on('bw_plantaciones');
            $table->foreign('user_insert', 'fk_bw_cosechas_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
            $table->foreign('cod_usuario_cosecha', 'fk_bw_cosechas_usu_usuarios_cosecha')->references('cod_usuario')->on('usu_usuarios');
            $table->foreign('cod_usuario_planificacion', 'fk_bw_cosechas_usu_usuarios_planificacion')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bw_cosechas');
        Schema::enableForeignKeyConstraints();
    }
};
