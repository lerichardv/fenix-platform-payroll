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

        Schema::create('geo_zonas', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_pais');
            $table->integer('cod_departamento');
            $table->integer('cod_municipio');
            $table->integer('cod_ciudad');
            $table->integer('cod_zona');
            $table->string('zona', 45);
            $table->string('acronimo', 10);
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->primary(['cod_pais', 'cod_departamento', 'cod_municipio', 'cod_ciudad', 'cod_zona']);

            $table->index(['cod_pais', 'cod_departamento', 'cod_municipio', 'cod_ciudad'], 'fk_geo_zonas_geo_ciudades1_idx');
            $table->index('user_insert', 'fk_geo_zonas_usu_usuarios1_idx');

            $table->foreign(['cod_pais', 'cod_departamento', 'cod_municipio', 'cod_ciudad'], 'fk_geo_zonas_geo_ciudades1')->references(['cod_pais', 'cod_departamento', 'cod_municipio', 'cod_ciudad'])->on('geo_ciudades');
            $table->foreign('user_insert', 'fk_geo_zonas_usu_usuarios1')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('geo_zonas');
        Schema::enableForeignKeyConstraints();
    }
};
