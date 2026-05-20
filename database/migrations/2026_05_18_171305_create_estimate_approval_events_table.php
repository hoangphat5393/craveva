<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('estimate_approval_events')) {
            return;
        }

        Schema::create('estimate_approval_events', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('company_id')->index();
            $table->unsignedInteger('estimate_id')->index();
            $table->string('event_type', 40);
            $table->unsignedInteger('actor_user_id')->nullable()->index();
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('estimate_id')
                ->references('id')
                ->on('estimates')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estimate_approval_events');
    }
};
