<?php

use App\Models\QRCodeScan;
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
        Schema::table('qrcode_scans', function (Blueprint $table) {
            $table->string('hour')->index()->nullable();
        });

        $this->calculateHourForAllScans();
    }

    private function calculateHourForAllScans()
    {
        QRCodeScan::all()->each(fn ($s) => $s->syncHour());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('qrcode_scans', function (Blueprint $table) {
            $table->dropIndex('qrcode_scans_hour_index');
            $table->dropColumn('hour');
        });
    }
};
