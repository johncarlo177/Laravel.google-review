<?php

use App\Models\QRCode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        Schema::table('qrcodes', function (Blueprint $table) {
            //
            $table->timestamp(
                'archived_at'
            )
                ->nullable()
                ->index();
        });

        $this->syncArchivedAtDatesForExistingQRCodes();
    }

    protected function syncArchivedAtDatesForExistingQRCodes()
    {
        DB::table('qrcodes')
            ->where('archived', true)->update([
                'archived_at' => now()
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('qrcodes', function (Blueprint $table) {
            //
            $table->dropIndex(['archived_at']);
            $table->dropColumn('archived_at');
        });
    }
};
