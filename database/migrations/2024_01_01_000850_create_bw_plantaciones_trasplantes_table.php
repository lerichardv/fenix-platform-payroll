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

        Schema::create('bw_plantaciones_trasplantes', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_trasplante', true, false);
            $table->integer('cod_plantacion_envia');
            $table->integer('cod_plantacion_recibe');
            $table->text('cod_bloques');
            $table->decimal('cantidad', 11, 3);
            $table->text('observacion');
            $table->text('cod_bloques_trasplante')->nullable();
            $table->string('numero_carga', 8)->nullable()->default(null);
            $table->date('fecha_trasplante')->nullable()->default(null);
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('cod_plantacion_envia', 'fk_bw_plan_tras_bw_plan1_idx');
            $table->index('cod_plantacion_recibe', 'fk_bw_plan_tras_bw_plan2_idx');
            $table->index('user_insert', 'fk_bw_plan_tras_usu_usuarios_idx');

            $table->foreign('user_insert', 'fk_bw_plan_tras_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bw_plantaciones_trasplantes');
        Schema::enableForeignKeyConstraints();
    }
};
