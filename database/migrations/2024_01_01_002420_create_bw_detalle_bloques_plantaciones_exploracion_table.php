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

        Schema::create('bw_detalle_bloques_plantaciones_exploracion', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_exploracion', true, false);
            $table->integer('cod_detalle');
            $table->integer('etapa_crecimiento')->default('1');
            $table->date('fecha_exploracion')->nullable()->default(null);
            $table->decimal('gusanos', 6, 3)->default('0.000');
            $table->decimal('huevos', 6, 3)->default('0.000');
            $table->decimal('saltahojas', 6, 3)->default('0.000');
            $table->decimal('afidos', 6, 3)->default('0.000');
            $table->decimal('chinches', 6, 3)->default('0.000');
            $table->decimal('moscos', 6, 3)->default('0.000');
            $table->decimal('escarabajos', 6, 3)->default('0.000');
            $table->decimal('acaros', 6, 3)->default('0.000');
            $table->decimal('cercospora_leaf_spot', 6, 3)->default('0.000');
            $table->decimal('pythium', 6, 3)->default('0.000');
            $table->decimal('rhizoctonia', 6, 3)->default('0.000');
            $table->decimal('bacteria', 6, 3)->default('0.000');
            $table->decimal('sclerotinia', 6, 3)->default('0.000');
            $table->decimal('alternaria_specks', 6, 3)->default('0.000');
            $table->decimal('mildew', 6, 3)->default('0.000');
            $table->decimal('virus', 6, 3)->default('0.000');
            $table->decimal('dolar', 6, 3)->default('0.000');
            $table->decimal('frogs_bit', 6, 3)->default('0.000');
            $table->decimal('plantas_lodo', 6, 3)->default('0.000');
            $table->decimal('tripa_pollo', 6, 3)->default('0.000');
            $table->decimal('zacate', 6, 3)->default('0.000');
            $table->decimal('hojas_danadas', 6, 3)->default('0.000');
            $table->decimal('tallos_purpuras', 6, 3)->default('0.000');
            $table->decimal('berro_enraizado', 6, 3)->default('0.000');
            $table->decimal('spidermites', 6, 3)->default('0.000');
            $table->decimal('white_rust', 6, 3)->default('0.000');
            $table->decimal('salt_accumulation', 6, 3)->default('0.000');
            $table->decimal('nutsedge', 6, 3)->default('0.000');
            $table->decimal('buds', 6, 3)->default('0.000');
            $table->decimal('zigzag_stems', 6, 3)->default('0.000');
            $table->decimal('nutrient_deficiency', 6, 3)->default('0.000');
            $table->decimal('round_up', 6, 3)->default('0.000');
            $table->decimal('light_color', 6, 3)->default('0.000');
            $table->decimal('mealybugs', 6, 3)->default('0.000');
            $table->decimal('thrips', 6, 3)->default('0.000');
            $table->text('observacion')->nullable();
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent()->useCurrentOnUpdate();

            $table->index('cod_detalle', 'fk_bw_det_blo_pla_exp_bw_det_blo_pla_idx');
            $table->index('user_insert', 'fk_bw_det_blo_pla_exp_usu_usuarios_idx');

            $table->foreign('cod_detalle', 'fk_bw_det_blo_pla_exp_bw_det_blo_pla')->references('cod_detalle')->on('bw_detalle_bloques_plantaciones');
            $table->foreign('user_insert', 'fk_bw_det_blo_pla_exp_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bw_detalle_bloques_plantaciones_exploracion');
        Schema::enableForeignKeyConstraints();
    }
};
