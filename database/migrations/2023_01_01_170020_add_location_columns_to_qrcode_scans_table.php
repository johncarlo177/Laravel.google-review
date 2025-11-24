<?php

use App\Models\QRCodeScan;
use App\Support\MaxMind\MaxMindResolver;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
            DB::statement('ALTER TABLE qrcode_scans MODIFY qrcode_redirect_id BIGINT UNSIGNED DEFAULT NULL');

            $this->createStringColumns([
                'iso_code',
                'city',
                'country',
                'latitude',
                'longitude',
                'timezone',
            ], $table);

            $table->integer('accuracy_radius')->unsigned()->index()->nullable();
        });

        $this->fillLocationFieldsInAllScans();
    }

    private function fillLocationFieldsInAllScans()
    {
        try {
            $scans = QRCodeScan::all();

            foreach ($scans as $scan) {
                $resolver = new MaxMindResolver();

                $location = $resolver->resolve($scan->ip_address);

                if ($location) {
                    $scan->fillLocationData($location);
                    $scan->save();
                }
            }
        } catch (Throwable $ex) {
            Log::error('Cannot fill all location data of scans during the update. ' . $ex->getMessage());
        }
    }

    private function createStringColumns($names, Blueprint $table)
    {
        foreach ($names as $name) {
            $table->string($name)->index()->nullable();
        }
    }

    private function dropColumns($names, Blueprint $table)
    {
        foreach ($names as $name) {
            $table->dropIndex(
                sprintf('qrcode_scans_%s_index', $name)
            );

            $table->dropColumn($name);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('qrcode_scans', function (Blueprint $table) {
            $this->dropColumns([
                'iso_code',
                'city',
                'country',
                'latitude',
                'longitude',
                'timezone',
                'accuracy_radius'
            ], $table);
        });
    }
};
