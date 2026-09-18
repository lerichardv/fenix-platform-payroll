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

        Schema::create('bw_info_empresa', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_info_empresa', true, false);
            $table->integer('cod_gerencia');
            $table->string('nombre_empresa', 100);
            $table->string('lema_empresa', 200)->nullable()->default(null);
            $table->string('direccion_linea_1', 100);
            $table->string('direccion_linea_2', 45)->nullable()->default(null);
            $table->string('telefono_empresa', 45);
            $table->string('correo_empresa', 100);
            $table->string('fax_empresa', 45)->nullable()->default(null);
            $table->text('descripcion_empresa')->nullable();
            $table->text('logo_empresa')->nullable();
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('user_insert', 'fk_bw_info_empresa_usu_usuarios_idx');
            $table->index('cod_gerencia', 'fk_bw_info_empresa_usu_gerencias_idx');

            $table->foreign('cod_gerencia', 'fk_bw_info_empresa_usu_gerencias')->references('cod_gerencia')->on('usu_gerencias');
            $table->foreign('user_insert', 'fk_bw_info_empresa_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bw_info_empresa');
        Schema::enableForeignKeyConstraints();
    }
};
