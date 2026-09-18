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

        Schema::create('doc_documentos_repositorio', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_documento', true, false);
            $table->integer('cod_gerencia');
            $table->integer('cod_tipo_documento');
            $table->string('titulo_documento', 100);
            $table->text('descripcion');
            $table->text('palabras_claves');
            $table->string('archivo_PDF', 400);
            $table->integer('contador_descargas')->default('0');
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('user_insert', 'fk_doc_documentos_repositorio_usu_usuarios1_idx');
            $table->index('cod_tipo_documento', 'fk_doc_documentos_repositorio_doc_tipo_documentos1_idx');
            $table->index('cod_gerencia', 'fk_doc_documentos_repositorio_usu_gerencias1_idx');

            $table->foreign('cod_tipo_documento', 'fk_doc_documentos_repositorio_doc_tipo_documentos1')->references('cod_tipo_documento')->on('doc_tipo_documentos');
            $table->foreign('cod_gerencia', 'fk_doc_documentos_repositorio_usu_gerencias1')->references('cod_gerencia')->on('usu_gerencias');
            $table->foreign('user_insert', 'fk_doc_documentos_repositorio_usu_usuarios1')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('doc_documentos_repositorio');
        Schema::enableForeignKeyConstraints();
    }
};
