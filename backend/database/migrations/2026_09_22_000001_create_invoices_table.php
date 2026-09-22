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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('series', 10)->default('FAC');
            $table->unsignedBigInteger('folio');
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();

            // Receptor (Customer fiscal data)
            $table->string('rfc_receptor', 13);
            $table->string('razon_social_receptor');
            $table->string('regimen_fiscal_receptor', 10);
            $table->string('codigo_postal_receptor', 5);
            $table->string('uso_cfdi', 10);

            // Payment and Currency
            $table->string('forma_pago', 5); // 01, 03, 04, 28, etc.
            $table->string('metodo_pago', 5)->default('PUE'); // PUE o PPD
            $table->string('moneda', 5)->default('MXN');
            $table->decimal('tipo_cambio', 8, 4)->default(1.0000);

            // Financials (SAT amounts)
            $table->decimal('subtotal', 12, 2);
            $table->decimal('iva_trasladado', 12, 2);
            $table->decimal('total', 12, 2);

            // Digital Stamp & Certification
            $table->string('no_certificado_emisor', 30);
            $table->string('no_certificado_sat', 30);
            $table->dateTime('fecha_emision');
            $table->dateTime('fecha_timbrado');
            $table->text('sello_emisor');
            $table->text('sello_sat');
            $table->text('cadena_original');
            $table->text('qr_code_url');

            // XML Storage and Lifecycle
            $table->mediumText('xml_content');
            $table->enum('status', ['vigente', 'cancelada'])->default('vigente');
            $table->dateTime('fecha_cancelacion')->nullable();
            $table->string('motivo_cancelacion', 5)->nullable(); // 01, 02, 03, 04

            $table->timestamps();

            // Indexes for fast lookup
            $table->index(['series', 'folio']);
            $table->index('rfc_receptor');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
