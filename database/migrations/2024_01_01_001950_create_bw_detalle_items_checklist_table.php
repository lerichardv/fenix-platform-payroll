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

        Schema::create('bw_detalle_items_checklist', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_detalle_item', true, false);
            $table->integer('cod_item_check_list');
            $table->string('texto_item', 50);
            $table->decimal('valor_item', 6, 3)->default('0.000');
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('user_insert', 'bw_det_items_checklist_usu_usuarios_idx');
            $table->index('cod_item_check_list', 'bw_det_items_checklist_bt_items_checklist_idx');

            $table->foreign('cod_item_check_list', 'bw_det_items_checklist_bt_items_checklist')->references('cod_item')->on('bw_items_checklist');
            $table->foreign('user_insert', 'bw_det_items_checklist_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bw_detalle_items_checklist');
        Schema::enableForeignKeyConstraints();
    }
};
