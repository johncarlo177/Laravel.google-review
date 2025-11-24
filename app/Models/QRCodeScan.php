<?php

namespace App\Models;

use Carbon\Carbon;
use App\Support\MaxMind\Location;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


/**
 * @property int id
 * @property string hour
 * @property string ip_address
 * @property string device_name
 * @property string device_brand
 * @property string device_model
 * @property string os_name
 * @property string os_version
 * @property string browser
 * @property string language
 * @property string client_type
 * @property string client_name
 * @property string client_version
 * @property string iso_code
 * @property string city
 * @property string country
 * @property float accuracy_radius
 * @property string latitude
 * @property string longitude
 * @property string timezone
 * @property int qrcode_id
 * @property Carbon created_at
 * @property Carbon updated_at
 */

class QRCodeScan extends Model
{
    use HasFactory;

    protected $table = 'qrcode_scans';

    protected $casts = [
        'device_info' => 'array'
    ];

    public function qrcode_redirect()
    {
        return $this->belongsTo(QRCodeRedirect::class, 'qrcode_redirect_id');
    }

    public function fillLocationData(Location $location)
    {
        $this->iso_code = $location->iso_code;
        $this->city = $location->city;
        $this->country = $location->country;
        $this->accuracy_radius = $location->accuracy_radius;
        $this->latitude = $location->latitude;
        $this->longitude = $location->longitude;
        $this->timezone = $location->timezone;
    }

    public function calculateHour()
    {
        if (!$this->timezone) return;

        $date = $this->created_at;

        if (!$date) {
            $date = Carbon::now();
        }

        $date->setTimezone($this->timezone);

        $this->hour = $date->format('H');
    }

    public function syncHour()
    {
        if (!$this->timezone) return;

        $this->calculateHour();

        $this->save();
    }
}
