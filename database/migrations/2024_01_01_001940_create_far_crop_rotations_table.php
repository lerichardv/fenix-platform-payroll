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

        Schema::create('far_crop_rotations', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_rotations', true, false);
            $table->integer('cod_plantacion');
            $table->integer('cod_transplante');
            $table->integer('cod_field');
            $table->integer('cod_semilla');
            $table->integer('cod_farm');
            $table->integer('cod_temporada');
            $table->date('planting_date');
            $table->text('comentarios')->nullable();
            $table->tinyInteger('completado')->default('0');
            $table->integer('cod_bloque')->default('0');
            $table->string('bloque', 45)->default('0');
            $table->tinyInteger('reseteado')->default('0');
            $table->decimal('ini_acres', 3, 2)->default('0.00');
            $table->decimal('use_acres', 3, 2)->default('0.00');
            $table->decimal('pra_acres', 3, 2)->default('0.00');
            $table->decimal('teo_acres', 3, 2)->default('0.00');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->unique('cod_rotations', 'cod_rotations_UNIQUE');

            $table->index('cod_semilla', 'fk_far_crop_rotations_bw_inventario_semilla1_idx');
            $table->index('cod_plantacion', 'fk_far_crop_rotations_bw_inventario_plantaciones1_idx');
            $table->index('user_insert', 'fk_far_crop_rotations_usu_usuarios1_idx');
            $table->index('cod_temporada', 'fk_far_crop_rotations_far_temporadas1_idx');
            $table->index('cod_transplante', 'fk_far_crop_rotations_bw_inventario_trasplantes1_idx');
            $table->index(['cod_field', 'cod_farm'], 'fk_far_crop_rotations_far_fields1_idx');

            $table->foreign('cod_plantacion', 'fk_far_crop_rotations_bw_inventario_plantaciones1')->references('cod_plantacion')->on('bw_inventario_plantaciones');
            $table->foreign('cod_semilla', 'fk_far_crop_rotations_bw_inventario_semilla1')->references('cod_inventario')->on('bw_inventario_semilla');
            $table->foreign(['cod_field', 'cod_farm'], 'fk_far_crop_rotations_far_fields1')->references(['cod_field', 'cod_farm'])->on('far_fields');
            $table->foreign('cod_temporada', 'fk_far_crop_rotations_far_temporadas1')->references('cod_temporada')->on('far_temporadas');
            $table->foreign('user_insert', 'fk_far_crop_rotations_usu_usuarios1')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('far_crop_rotations');
        Schema::enableForeignKeyConstraints();
    }
};
