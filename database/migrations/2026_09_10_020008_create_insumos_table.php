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
        Schema::create('insumos', function (Blueprint $table) {
            $table->id('insumo_id');
            $table->string('nombre', 150);
            $table->foreignId('categoria_id')->constrained('categorias', 'categoria_id')->onDelete('restrict');
            $table->decimal('precio', 10, 2);
            $table->foreignId('unidad_medida_id')->constrained('unidades_medida', 'unidad_medida_id')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insumos');
    }
};
