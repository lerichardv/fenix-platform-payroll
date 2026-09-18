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

        Schema::create('bw_zonas', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_zona')->primary();
            $table->integer('cod_info_empresa');
            $table->string('zona', 50);
            $table->string('abreviatura', 4);
            $table->string('ubicacion', 150)->nullable()->default(null);
            $table->string('bloque_inicial', 15)->nullable()->default(null);
            $table->string('bloque_final', 15)->nullable()->default(null);
            $table->decimal('cantidad_acres', 11, 3)->nullable()->default(null);
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('cod_info_empresa', 'fk_bw_zonas_usu_gerencias_idx');
            $table->index('user_insert', 'fk_bw_zonas_usu_usuarios_idx');

            $table->foreign('cod_info_empresa', 'fk_bw_zonas_bw_info_empresa')->references('cod_info_empresa')->on('bw_info_empresa');
            $table->foreign('user_insert', 'fk_bw_zonas_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bw_zonas');
        Schema::enableForeignKeyConstraints();
    }
};
