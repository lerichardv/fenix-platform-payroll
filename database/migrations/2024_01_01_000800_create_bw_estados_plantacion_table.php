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

        Schema::create('bw_estados_plantacion', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_estado_plantacion')->primary();
            $table->integer('cod_estado_padre')->nullable()->default(null);
            $table->string('estado_plantacion', 45);
            $table->string('estado_plantacion_english', 45)->nullable()->default(null);
            $table->integer('tiempo_espera')->nullable()->default(null);
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('user_insert', 'fk_bw_estados_plantacion_usu_usuarios_idx');

            $table->foreign('user_insert', 'fk_bw_estados_plantacion_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bw_estados_plantacion');
        Schema::enableForeignKeyConstraints();
    }
};
