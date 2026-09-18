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

        Schema::create('pay_tipo_packs', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_tipo_pack', true, false);
            $table->string('tipo_pack', 45);
            $table->integer('cantidad')->nullable()->default('0');
            $table->decimal('piece_rate', 8, 2)->default('0.00');
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->unique('cod_tipo_pack', 'cod_tipo_pack_UNIQUE');

            $table->index('user_insert', 'fk_pay_tipo_packs_usu_usuarios1_idx');

            $table->foreign('user_insert', 'fk_pay_tipo_packs_usu_usuarios1')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('pay_tipo_packs');
        Schema::enableForeignKeyConstraints();
    }
};
