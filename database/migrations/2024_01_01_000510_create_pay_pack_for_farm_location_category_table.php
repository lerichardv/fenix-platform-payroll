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

        Schema::create('pay_pack_for_farm_location_category', function (Blueprint $table) {
            $table->engine = 'MyISAM';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('id', true, false);
            $table->integer('cod_tipo_pack');
            $table->integer('cod_farm');
            $table->integer('cod_location');
            $table->integer('cod_categoria');
            $table->tinyInteger('activo')->nullable()->default('1');
            $table->tinyInteger('visible')->nullable()->default('1');

            $table->unique('id', 'id_UNIQUE');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('pay_pack_for_farm_location_category');
        Schema::enableForeignKeyConstraints();
    }
};
