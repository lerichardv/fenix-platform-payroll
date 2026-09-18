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

        Schema::create('bw_reporte_carga_plantacion', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_reporte', true, false);
            $table->integer('cod_plantacion');
            $table->text('cod_bloques_plantacion')->nullable();
            $table->integer('cod_inventario_semilla');
            $table->string('num_orden_compra', 10)->nullable()->default(null);
            $table->dateTime('fecha_orden')->nullable()->default(null);
            $table->string('num_lote', 10)->nullable()->default(null);
            $table->string('num_lote_ranch', 10)->nullable()->default(null);
            $table->decimal('inicial_size_harvest', 7, 3)->nullable()->default(null);
            $table->decimal('final_size_harvest', 7, 3)->nullable()->default(null);
            $table->integer('dark_green_color')->nullable()->default(null);
            $table->integer('yellow_leaves')->nullable()->default(null);
            $table->integer('weeds')->nullable()->default(null);
            $table->integer('optimal_soil_water_capacity')->nullable()->default(null);
            $table->integer('right_density')->nullable()->default(null);
            $table->text('other_defects')->nullable();
            $table->decimal('size_range_porcentage', 6, 3)->nullable()->default(null);
            $table->decimal('inicial_size_range', 6, 3)->nullable()->default(null);
            $table->decimal('final_size_range', 6, 3)->nullable()->default(null);
            $table->tinyInteger('select_size_range')->nullable()->default(null);
            $table->string('size_range1', 45)->nullable()->default(null);
            $table->tinyInteger('select_size_range1')->nullable()->default(null);
            $table->string('size_range2', 45)->nullable()->default(null);
            $table->tinyInteger('select_size_range2')->nullable()->default(null);
            $table->string('other_defects_ha', 45)->nullable()->default(null);
            $table->tinyInteger('select_other_defects_ha')->nullable()->default(null);
            $table->tinyInteger('dew_leaf')->nullable()->default(null);
            $table->dateTime('inicial_time_harvest')->nullable()->default(null);
            $table->dateTime('final_time_harvest')->nullable()->default(null);
            $table->decimal('temperature_product', 7, 3)->nullable()->default(null);
            $table->decimal('inicial_average_tote_weight_reported', 7, 3)->nullable()->default(null);
            $table->decimal('final_average_tote_weight_reported', 7, 3)->nullable()->default(null);
            $table->decimal('real_average_tote_weight', 7, 3)->nullable()->default(null);
            $table->dateTime('time_receiving')->nullable()->default(null);
            $table->decimal('total_load_lbs_goal', 11, 3)->nullable()->default(null);
            $table->decimal('load_weight_received', 11, 3)->nullable()->default(null);
            $table->decimal('average_tote_weight', 12, 3)->nullable()->default(null);
            $table->decimal('temperature_receiving', 7, 3)->nullable()->default(null);
            $table->dateTime('time_vacuum_cooler')->nullable()->default(null);
            $table->decimal('temperature_vacuum_cooler', 7, 3)->nullable()->default(null);
            $table->tinyInteger('hydrocooling')->nullable()->default(null);
            $table->dateTime('time_pickup')->nullable()->default(null);
            $table->decimal('tlc', 7, 3)->nullable()->default(null);
            $table->decimal('vacuum_cooler', 7, 3)->nullable()->default(null);
            $table->dateTime('pickup_truck_checkin')->nullable()->default(null);
            $table->tinyInteger('number_cut')->nullable()->default(null);
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('cod_plantacion', 'fk_bw_rep_carga_plantacion_bw_plantaciones_idx');
            $table->index('cod_inventario_semilla', 'fk_bw_rep_carga_plantacion_bw_inventario_semilla_idx');
            $table->index('user_insert', 'fk_bw_rep_carga_plantacion_usu_usuarios_idx');

            $table->foreign('cod_inventario_semilla', 'fk_bw_rep_carga_plantacion_bw_inventario_semilla')->references('cod_inventario')->on('bw_inventario_semilla');
            $table->foreign('cod_plantacion', 'fk_bw_rep_carga_plantacion_bw_plantaciones')->references('cod_plantacion')->on('bw_plantaciones');
            $table->foreign('user_insert', 'fk_bw_rep_carga_plantacion_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bw_reporte_carga_plantacion');
        Schema::enableForeignKeyConstraints();
    }
};
