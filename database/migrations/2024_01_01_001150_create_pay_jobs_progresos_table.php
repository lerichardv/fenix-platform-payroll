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

        Schema::create('pay_jobs_progresos', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_job', true, false);
            $table->integer('cod_job_local')->default('0');
            $table->integer('identificador_unico_local')->nullable()->default('1');
            $table->integer('cod_harvest')->nullable()->default(null);
            $table->integer('cod_miscellaneous')->nullable()->default(null);
            $table->integer('cod_estado_job');
            $table->date('fecha_job');
            $table->time('hora_inicio')->nullable()->default(null);
            $table->time('hora_final')->nullable()->default(null);
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('cod_harvest', 'fk_pay_jobs_progresos_pay_harvests1_idx');
            $table->index('user_insert', 'fk_pay_jobs_progresos_usu_usuarios1_idx');
            $table->index('cod_harvest', 'idx_pay_jobs_progresos_cod_harvest');
            $table->index('cod_estado_job', 'idx_pay_jobs_progresos_cod_estado_job');
            $table->index('cod_miscellaneous', 'idx_pay_jobs_progresos_cod_miscellaneous');

            $table->foreign('user_insert', 'fk_pay_jobs_progresos_usu_usuarios1')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('pay_jobs_progresos');
        Schema::enableForeignKeyConstraints();
    }
};
