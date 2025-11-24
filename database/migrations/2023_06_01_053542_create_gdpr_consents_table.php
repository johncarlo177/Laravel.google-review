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
        Schema::create('gdpr_consents', function (Blueprint $table) {
            $table->increments('id');
            $table->string('cookie_consent_name', 255);
            $table->string('consent_id', 255);
            $table->text('cookie_consent_value');
            $table->string('consent_necessary', 255);
            $table->string('consent_preferences', 255);
            $table->string('consent_statistics', 255);
            $table->string('consent_marketing', 255);
            $table->string('consent_unclassified', 255);
            $table->string('consent_url', 255);
            $table->string('consent_ip_anonymized', 255);
            $table->string('consent_country_isocode', 255);
            $table->text('consent_user_agent');
            $table->bigInteger('cookie_consent_lifetime');
            $table->dateTime('consent_client_datetime');
            $table->dateTime('consent_server_datetime');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gdpr_consents');
    }
};
