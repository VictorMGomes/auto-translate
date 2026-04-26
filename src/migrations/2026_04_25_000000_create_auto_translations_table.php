<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auto_translations', function (Blueprint $table) {
            $table->id();
            $table->string('translatable_type');
            $table->string('translatable_id');
            $table->string('field');
            $table->string('locale', 10);
            $table->text('content');
            $table->timestamps();

            $table->index(['translatable_type', 'translatable_id'], 'auto_trans_morph_index');
            $table->unique(['translatable_type', 'translatable_id', 'field', 'locale'], 'auto_trans_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auto_translations');
    }
};
