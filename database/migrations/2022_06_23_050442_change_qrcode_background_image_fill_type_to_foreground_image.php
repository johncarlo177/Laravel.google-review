<?php

use App\Models\QRCode;
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
        QRCode::all()->each(function ($qrcode) {
            if ($qrcode->design->fillType === 'background_image') {
                $design = $qrcode->design;

                $design->fillType = 'foreground_image';
                $qrcode->design = $design;
                $qrcode->save();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('foreground_image', function (Blueprint $table) {
            //
        });
    }
};
