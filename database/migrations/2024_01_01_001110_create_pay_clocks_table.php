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

        Schema::create('pay_clocks', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_clock', true, false);
            $table->integer('cod_estado');
            $table->integer('cod_empleado');
            $table->tinyInteger('clock_in')->default('1');
            $table->tinyInteger('clock_out')->default('0');
            $table->date('fecha');
            $table->timestamp('fecha_clock_out')->nullable()->default(null);
            $table->time('hora');
            $table->string('GPS', 345);
            $table->timestamp('date_insert')->useCurrent();

            $table->index('cod_empleado', 'fk_pay_clocks_usu_usuarios1_idx');
            $table->index('cod_estado', 'fk_pay_clocks_bw_inventario_estado_plantacion1_idx');

            $table->foreign('cod_estado', 'fk_pay_clocks_bw_inventario_estado_plantacion1')->references('cod_estado')->on('bw_inventario_estados_plantaciones');
            $table->foreign('cod_empleado', 'fk_pay_clocks_usu_usuarios1')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('pay_clocks');
        Schema::enableForeignKeyConstraints();
    }
};
