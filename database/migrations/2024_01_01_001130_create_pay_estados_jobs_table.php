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

        Schema::create('pay_estados_jobs', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_estado_job', true, false);
            $table->string('estado_job', 45);
            $table->string('activo', 45)->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('user_insert', 'fk_pay_estados_jobs_usu_usuarios1_idx');
            $table->index('estado_job', 'idx_pay_estados_jobs_estado_job');
            $table->index('cod_estado_job', 'idx_pay_estados_jobs_cod_estado_job');

            $table->foreign('user_insert', 'fk_pay_estados_jobs_usu_usuarios1')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('pay_estados_jobs');
        Schema::enableForeignKeyConstraints();
    }
};
