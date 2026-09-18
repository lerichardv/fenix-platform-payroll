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

        Schema::create('bw_bloques', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_bloque', true, false);
            $table->integer('cod_zona');
            $table->string('clave_bloque', 10)->nullable()->default(null);
            $table->integer('nombre_bloque');
            $table->decimal('num_acres', 7, 3);
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('user_insert', 'fk_bw_bloques_usu_usuarios_idx');
            $table->index('cod_zona', 'fk_bw_bloques_bw_zonas_idx');

            $table->foreign('cod_zona', 'fk_bw_bloques_bw_zonas')->references('cod_zona')->on('bw_zonas');
            $table->foreign('user_insert', 'fk_bw_bloques_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bw_bloques');
        Schema::enableForeignKeyConstraints();
    }
};
