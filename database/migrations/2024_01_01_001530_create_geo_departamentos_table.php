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

        Schema::create('geo_departamentos', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_pais');
            $table->integer('cod_departamento');
            $table->string('departamento', 45);
            $table->string('acronimo', 10);
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->primary(['cod_pais', 'cod_departamento']);

            $table->index('cod_pais', 'fk_geo_departamentos_geo_paises1_idx');
            $table->index('user_insert', 'fk_geo_departamentos_usu_usuarios1_idx');

            $table->foreign('cod_pais', 'fk_geo_departamentos_geo_paises1')->references('cod_pais')->on('geo_paises');
            $table->foreign('user_insert', 'fk_geo_departamentos_usu_usuarios1')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('geo_departamentos');
        Schema::enableForeignKeyConstraints();
    }
};
