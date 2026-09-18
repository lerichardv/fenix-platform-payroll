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

        Schema::create('bw_reporte_carga', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_reporte', true, false);
            $table->integer('cod_producto');
            $table->string('num_orden_compra', 10)->nullable()->default(null);
            $table->dateTime('fecha_orden')->nullable()->default(null);
            $table->string('num_lote', 10)->nullable()->default(null);
            $table->string('num_lote_ranch', 10)->nullable()->default(null);
            $table->decimal('inicial_size_harvest', 7, 3)->nullable()->default(null);
            $table->decimal('final_size_harvest', 7, 3)->nullable()->default(null);
            $table->tinyInteger('dark_green_color')->nullable()->default(null);
            $table->tinyInteger('yellow_leaves')->nullable()->default(null);
            $table->tinyInteger('select_yellow_leaves')->nullable()->default(null);
            $table->tinyInteger('weeds')->nullable()->default(null);
            $table->tinyInteger('optimal_soil_water_capacity')->nullable()->default(null);
            $table->tinyInteger('right_density')->nullable()->default(null);
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
            $table->decimal('acres_cosechados', 9, 3)->nullable()->default(null);
            $table->decimal('yellow_leaves2', 7, 3)->nullable()->default(null);
            $table->text('comentarios')->nullable();
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('cod_producto', 'fk_bw_reporte_carga_qua_productos_idx');
            $table->index('user_insert', 'fk_bw_reporte_carga_usu_usuarios_idx');

            $table->foreign('cod_producto', 'fk_bw_reporte_carga_qua_productos')->references('cod_producto')->on('qua_productos');
            $table->foreign('user_insert', 'fk_bw_reporte_carga_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bw_reporte_carga');
        Schema::enableForeignKeyConstraints();
    }
};
