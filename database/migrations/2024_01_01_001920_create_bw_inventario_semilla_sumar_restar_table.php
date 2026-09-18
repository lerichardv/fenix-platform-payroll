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

        Schema::create('bw_inventario_semilla_sumar_restar', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_sumar_restar', true, false);
            $table->integer('cod_inventario');
            $table->dateTime('fecha');
            $table->decimal('cantidad_sumar', 11, 3)->default('0.000');
            $table->decimal('cantidad_restar', 11, 3)->default('0.000');
            $table->text('razon');
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('cod_inventario', 'fk_bw_inv_sem_su_res_bw_inv_sem_idx');
            $table->index('user_insert', 'fk_bw_inv_sem_su_res_usu_usuarios_idx');

            $table->foreign('cod_inventario', 'fk_bw_inv_sem_su_res_bw_inv_sem')->references('cod_inventario')->on('bw_inventario_semilla');
            $table->foreign('user_insert', 'fk_bw_inv_sem_su_res_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bw_inventario_semilla_sumar_restar');
        Schema::enableForeignKeyConstraints();
    }
};
