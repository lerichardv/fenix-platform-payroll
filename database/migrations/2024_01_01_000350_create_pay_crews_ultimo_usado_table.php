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

        Schema::create('pay_crews_ultimo_usado', function (Blueprint $table) {
            $table->engine = 'MyISAM';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_crew_ultimo_usado', true, false);
            $table->integer('cod_harvest')->nullable()->default(null);
            $table->integer('cod_miscellaneous')->nullable()->default(null);
            $table->integer('cod_empleado');
            $table->integer('pin');
            $table->integer('qc_pin');
            $table->integer('cod_supervisor');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->unique('cod_crew_ultimo_usado', 'cod_crew_UNIQUE');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('pay_crews_ultimo_usado');
        Schema::enableForeignKeyConstraints();
    }
};
