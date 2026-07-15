<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onboarding_requests', function (Blueprint $table) {
            $table->id();
            $table->integer('requested_by')->nullable();          // jefe que solicita

            $table->string('position');                            // cargo del funcionario
            $table->string('employee_name');                       // nombre del nuevo funcionario

            $table->string('entry_type');                          // 'nuevo' | 'reemplazo'
            $table->integer('replaces_user_id')->nullable();       // a quién reemplaza (si aplica)
            $table->unsignedBigInteger('asset_id')->nullable();    // equipo a asignar / reasignar

            $table->json('accessories')->nullable();               // ids de accesorios solicitados
            $table->json('platforms')->nullable();                 // plataformas requeridas

            $table->text('notes')->nullable();

            $table->string('status')->default('pending');          // pending|approved|rejected|fulfilled
            $table->integer('processed_by')->nullable();
            $table->dateTime('processed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onboarding_requests');
    }
};
