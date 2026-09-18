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

        Schema::create('doc_historial_descargas', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_historial', true, false);
            $table->integer('cod_documento');
            $table->integer('cod_tipo_dispositivo');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('cod_documento', 'fk_doc_historial_descargas_doc_documentos_repositorio1_idx');
            $table->index('cod_tipo_dispositivo', 'fk_doc_historial_descargas_ug_tipo_dispositivos1_idx');
            $table->index('user_insert', 'fk_doc_historial_descargas_usu_usuarios1_idx');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('doc_historial_descargas');
        Schema::enableForeignKeyConstraints();
    }
};
