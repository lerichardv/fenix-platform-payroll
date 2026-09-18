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

        Schema::create('ug_email_configuration', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_modulo');
            $table->integer('cod_email');
            $table->string('host', 45);
            $table->integer('port');
            $table->string('username', 45);
            $table->string('password', 200);
            $table->string('fromAddress', 200);
            $table->string('fromName', 200);
            $table->string('mensaje', 200);
            $table->string('subject', 60);
            $table->string('sistema', 45);
            $table->string('cuerpo_correo', 20000);
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();
            $table->string('image', 60);
            $table->string('correo_consulta', 60)->nullable()->default(null);
            $table->string('dk_domain', 45)->nullable()->default(null);

            $table->primary(['cod_modulo', 'cod_email']);

            $table->index('user_insert', 'fk_ug_email_configuration_usu_usuarios1_idx');

            $table->foreign('cod_modulo', 'fk_email')->references('cod_modulo')->on('ug_modulos');
            $table->foreign('user_insert', 'fk_ug_email_configuration_usu_usuarios1')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('ug_email_configuration');
        Schema::enableForeignKeyConstraints();
    }
};
