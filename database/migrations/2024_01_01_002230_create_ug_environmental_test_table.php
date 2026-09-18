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

        Schema::create('ug_environmental_test', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_test', true, false);
            $table->integer('cod_pais');
            $table->integer('cod_departamento');
            $table->integer('cod_municipio');
            $table->integer('cod_info_empresa');
            $table->integer('cod_location');
            $table->integer('cod_type_test');
            $table->integer('cod_source_phase');
            $table->integer('cod_sample');
            $table->string('sample_id', 75);
            $table->date('sample_date');
            $table->time('sample_time');
            $table->string('result', 75);
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('cod_pais', 'fk_ug_env_test_geo_paises_idx');
            $table->index(['cod_pais', 'cod_departamento'], 'fk_ug_env_test_geo_deptos_idx');
            $table->index(['cod_pais', 'cod_departamento', 'cod_municipio'], 'fk_ug_env_test_geo_muni_idx');
            $table->index('user_insert', 'fk_ug_env_test_usu_usuarios_idx');

            $table->foreign(['cod_pais', 'cod_departamento'], 'fk_ug_env_test_geo_deptos')->references(['cod_pais', 'cod_departamento'])->on('geo_departamentos');
            $table->foreign(['cod_pais', 'cod_departamento', 'cod_municipio'], 'fk_ug_env_test_geo_muni')->references(['cod_pais', 'cod_departamento', 'cod_municipio'])->on('geo_municipios');
            $table->foreign('cod_pais', 'fk_ug_env_test_geo_paises')->references('cod_pais')->on('geo_paises');
            $table->foreign('user_insert', 'fk_ug_env_test_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('ug_environmental_test');
        Schema::enableForeignKeyConstraints();
    }
};
