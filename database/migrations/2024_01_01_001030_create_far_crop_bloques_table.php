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

        Schema::create('far_crop_bloques', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_crop_bloques', true, false);
            $table->integer('cod_rotations')->nullable()->default('0');
            $table->integer('cod_farm');
            $table->integer('cod_field');
            $table->integer('cod_bloque');
            $table->tinyInteger('completado')->default('0');
            $table->string('bloque', 45);
            $table->decimal('ini_acres', 3, 2);
            $table->decimal('use_acres', 3, 2);
            $table->decimal('pra_acres', 3, 2);
            $table->decimal('teo_acres', 3, 2);
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->unique('cod_crop_bloques', 'cod_crop_bloques_UNIQUE');

            $table->index(['cod_bloque', 'cod_farm', 'cod_field'], 'fk_far_crop_bloques_far_bloques1_idx');
            $table->index('user_insert', 'fk_far_crop_bloques_usu_usuarios1_idx');

            $table->foreign('user_insert', 'fk_far_crop_bloques_usu_usuarios1')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('far_crop_bloques');
        Schema::enableForeignKeyConstraints();
    }
};
