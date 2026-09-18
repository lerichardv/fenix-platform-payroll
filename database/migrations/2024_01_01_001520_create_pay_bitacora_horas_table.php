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

        Schema::create('pay_bitacora_horas', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_bitacora_horas', true, false);
            $table->integer('cod_empleado');
            $table->integer('cod_farms');
            $table->integer('cod_harvest')->nullable()->default(null);
            $table->integer('cod_crew')->nullable()->default(null);
            $table->integer('cod_job')->nullable()->default(null);
            $table->time('hora_inicio_antes');
            $table->time('hora_inicio_actual');
            $table->time('hora_final_antes');
            $table->time('hora_final_actual');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('cod_empleado', 'fk_pay_bitacora_horas_usu_usuarios1_idx');
            $table->index('cod_farms', 'fk_pay_bitacora_horas_far_farms1_idx');
            $table->index('user_insert', 'fk_pay_bitacora_horas_usu_usuarios2_idx');

            $table->foreign('cod_farms', 'fk_pay_bitacora_horas_far_farms1')->references('cod_farms')->on('far_farms');
            $table->foreign('cod_empleado', 'fk_pay_bitacora_horas_usu_usuarios1')->references('cod_usuario')->on('usu_usuarios');
            $table->foreign('user_insert', 'fk_pay_bitacora_horas_usu_usuarios2')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('pay_bitacora_horas');
        Schema::enableForeignKeyConstraints();
    }
};
