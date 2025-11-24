<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('qrcode_scans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('qrcode_id')->constrained();

            $table->foreignId('qrcode_redirect_id')->constrained();

            $stringColumns = [
                'ip_address',
                'device_name',
                'device_brand',
                'device_model',
                'os_name',
                'os_version',
                'client_type',
                'client_name',
                'client_version',
            ];

            foreach ($stringColumns as $column) {
                $table->string($column)->index();
            }

            $table->timestamps();

            $table->index(['created_at', 'updated_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('qrcode_scans');
    }
};
