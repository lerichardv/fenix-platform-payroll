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

        Schema::create('pay_harvests', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_harvest', true, false);
            $table->integer('user_admin');
            $table->integer('cod_farm');
            $table->integer('cod_plantacion');
            $table->integer('crop_age');
            $table->integer('cod_tipo_pack');
            $table->integer('cod_tipo_pago');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->unique('cod_harvest', 'cod_harvest_UNIQUE');

            $table->index('user_insert', 'fk_pay_harvests_usu_usuarios1_idx');
            $table->index('cod_plantacion', 'idx_pay_harvests_cod_plantacion');
            $table->index('crop_age', 'idx_pay_harvests_crop_age');

            $table->foreign('user_insert', 'fk_pay_harvests_usu_usuarios1')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('pay_harvests');
        Schema::enableForeignKeyConstraints();
    }
};
