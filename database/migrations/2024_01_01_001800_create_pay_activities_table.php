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

        Schema::create('pay_activities', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_activity', true, false);
            $table->integer('cod_farms');
            $table->integer('cod_location');
            $table->string('codigo', 45)->nullable()->default(null);
            $table->string('activity', 100);
            $table->decimal('piece_rate', 8, 2)->default('0.00');
            $table->tinyInteger('activo')->default('1');
            $table->tinyInteger('visible')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('user_insert', 'fk_pay_activities_usu_usuarios1_idx');
            $table->index('cod_location', 'fk_pay_activities_far_locations1_idx');
            $table->index(['cod_farms', 'cod_location'], 'idx_cod_farms_cod_location');

            $table->foreign('cod_farms', 'fk_pay_activities_far_farms')->references('cod_farms')->on('far_farms');
            $table->foreign('cod_location', 'fk_pay_activities_far_locations1')->references('cod_location')->on('far_locations');
            $table->foreign('user_insert', 'fk_pay_activities_usu_usuarios1')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('pay_activities');
        Schema::enableForeignKeyConstraints();
    }
};
