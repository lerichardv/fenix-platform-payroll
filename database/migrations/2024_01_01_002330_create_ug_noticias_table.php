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

        Schema::create('ug_noticias', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_noticia', true, false);
            $table->text('titulo');
            $table->text('contenido');
            $table->timestamp('fecha_inicio')->nullable()->default(null);
            $table->timestamp('fecha_fin')->nullable()->default(null);
            $table->string('clase', 45)->nullable()->default(null);
            $table->string('imagen', 200)->nullable()->default(null);
            $table->integer('cod_formulario')->nullable()->default(null);
            $table->text('cod_info_empresa')->nullable();
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('fecha_insert')->useCurrent();

            $table->index('user_insert', 'fk_ug_noticias_usu_usuarios1_idx');
            $table->index('cod_formulario', 'fk_ug_noticias_ug_formularios_idx');

            $table->foreign('cod_formulario', 'fk_ug_noticias_ug_formularios')->references('cod_formulario')->on('ug_formularios');
            $table->foreign('user_insert', 'fk_ug_noticias_usu_usuarios1')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('ug_noticias');
        Schema::enableForeignKeyConstraints();
    }
};
