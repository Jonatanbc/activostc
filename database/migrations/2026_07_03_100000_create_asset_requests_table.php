<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_id')->index();
            $table->integer('requested_by')->nullable();

            // Questionnaire: who the equipment will be assigned to (manual data).
            $table->string('assignee_name');
            $table->string('assignee_id_number')->nullable();  // cédula
            $table->string('assignee_position')->nullable();    // cargo / área
            $table->string('assignee_location')->nullable();    // sede / ubicación

            // Email account: new one or replacing someone.
            $table->string('account_type')->nullable();          // 'nueva' | 'reemplazo'
            $table->string('replaces_person')->nullable();       // a quién reemplaza

            $table->date('needed_at')->nullable();
            $table->text('justification')->nullable();

            $table->string('status')->default('pending');        // pending|approved|rejected|fulfilled
            $table->integer('processed_by')->nullable();
            $table->dateTime('processed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_requests');
    }
};
