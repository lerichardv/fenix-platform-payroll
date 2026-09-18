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

        Schema::create('far_locations', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_location', true, false);
            $table->integer('cod_farms');
            $table->string('location', 60);
            $table->string('abreviacion', 45)->nullable()->default(null);
            $table->string('cost_center', 11)->default('00000');
            $table->string('labor_phase', 11)->default('00000');
            $table->tinyInteger('activo')->default('1');
            $table->tinyInteger('visible')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('user_insert', 'fk_far_locations_usu_usuarios1_idx');
            $table->index('cod_farms', 'fk_far_locations_far_farms1');
            $table->index(['cod_farms', 'location'], 'idx_cod_farms_location');
            $table->index(['activo', 'visible'], 'idx_activo_visible');

            $table->foreign('cod_farms', 'fk_far_locations_far_farms1')->references('cod_farms')->on('far_farms');
            $table->foreign('user_insert', 'fk_far_locations_usu_usuarios1')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('far_locations');
        Schema::enableForeignKeyConstraints();
    }
};
