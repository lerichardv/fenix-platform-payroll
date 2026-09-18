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

        Schema::create('pay_crews', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_crew', true, false);
            $table->integer('cod_harvest')->nullable()->default(null);
            $table->integer('cod_miscellaneous')->nullable()->default(null);
            $table->integer('cantidad_escaneos')->nullable()->default('0');
            $table->integer('cod_empleado');
            $table->time('hora_inicio')->nullable()->default(null);
            $table->time('hora_final')->nullable()->default(null);
            $table->integer('pin');
            $table->integer('qc_pin');
            $table->integer('cod_supervisor');
            $table->integer('cod_estado_job');
            $table->tinyInteger('registro_manual')->nullable()->default('0');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('cod_empleado', 'fk_pay_crews_usu_usuarios1_idx');
            $table->index('cod_supervisor', 'fk_pay_crews_usu_usuarios2_idx');
            $table->index('user_insert', 'fk_pay_crews_usu_usuarios3_idx');
            $table->index('cod_miscellaneous', 'idx_pay_crews_cod_miscellaneous');
            $table->index('cod_harvest', 'idx_pay_crews_cod_harvest');

            $table->foreign('cod_empleado', 'fk_pay_crews_usu_usuarios1')->references('cod_usuario')->on('usu_usuarios');
            $table->foreign('cod_supervisor', 'fk_pay_crews_usu_usuarios2')->references('cod_usuario')->on('usu_usuarios');
            $table->foreign('user_insert', 'fk_pay_crews_usu_usuarios3')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('pay_crews');
        Schema::enableForeignKeyConstraints();
    }
};
