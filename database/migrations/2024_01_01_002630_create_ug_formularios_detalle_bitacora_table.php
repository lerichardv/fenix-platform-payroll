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

        Schema::create('ug_formularios_detalle_bitacora', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_bitacora', true, false);
            $table->integer('cod_detalle');
            $table->integer('cod_formulario');
            $table->integer('cod_estado');
            $table->text('observacion')->nullable();
            $table->tinyInteger('prioridad')->default('0');
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->integer('user_review')->nullable()->default(null);
            $table->dateTime('date_insert');
            $table->integer('user_update');
            $table->timestamp('date_update')->useCurrent();

            $table->index('cod_detalle', 'fk_ug_form_det_bit_ug_form_det_idx');

            $table->foreign('cod_detalle', 'fk_ug_form_det_bit_ug_form_det')->references('cod_detalle')->on('ug_formularios_detalle');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('ug_formularios_detalle_bitacora');
        Schema::enableForeignKeyConstraints();
    }
};
