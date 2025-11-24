<?php

namespace App\Support;

use App\Interfaces\UserManager;
use App\Models\Config;
use App\Models\QRCode;
use App\Models\QRCodeRedirect;
use App\Models\User;

use App\Models\QRCodeScan as QRCodeScanModel;
use App\Plugins\PluginManager;
use App\Repositories\DeviceInfo;
use App\Support\MaxMind\MaxMindResolver;
use App\Support\System\Traits\HasClassSettings;
use Illuminate\Http\Request;

class QRCodeScanManager
{
    use HasClassSettings;

    private UserManager $users;

    protected static $recentlyCreatedScanRecord = null;

    public function __construct()
    {
        $this->users = app(UserManager::class);
    }

    /**
     * @return QRCodeScanModel
     */
    public static function getRecentlyCreatedScanRecord()
    {
        return static::$recentlyCreatedScanRecord;
    }

    public function countScansOfUserQRCodes(User $user)
    {
        $userIds = $this->users->getUserIdsOnTheSameSubscription($user);

        $qrcodesIds = QRCode::whereIn('user_id', $userIds)
            ->select('id')
            ->get()
            ->pluck('id');

        $redirectIds = QRCodeRedirect::whereIn('qrcode_id', $qrcodesIds)
            ->select('id')
            ->get()
            ->pluck('id');

        $scanCount = QRCodeScanModel::whereIn(
            'qrcode_redirect_id',
            $redirectIds
        )->count();

        return $scanCount;
    }

    public function syncCountOfQRCodeScans(QRCode $qrcode)
    {
        if (!$qrcode->redirect) return;

        if ($qrcode->scans_count) return;

        $qrcode->scans_count = $qrcode->redirect->scans()->count();

        if (!$qrcode->scans_count) return;

        $qrcode->save();
    }

    protected function increaseGlobalScans()
    {
        $this->setConfig('global_scans', $this->getGlobalScansCount() + 1);
    }

    public function getGlobalScansCount()
    {
        if (!$this->getConfig('global_scans')) {
            $count = collect(
                QRCode::pluck('scans_count')
            )->reduce(
                fn($total, $scans) => $total + $scans,
                0
            );

            $this->setConfig('global_scans', $count);
        }

        return $this->getConfig('global_scans');
    }

    private function increaseQRCodeScan(QRCode $qrcode)
    {
        $qrcode->scans_count += 1;

        $qrcode->save();
    }

    public function saveScanDetails($qrcodeId, $ip, $overrides = [])
    {
        $info = new DeviceInfo();

        $scan = new QRCodeScanModel;

        $scan->user_agent = $info->getUserAgent();

        $scan->qrcode_id = $qrcodeId;

        $scan->ip_address = $ip;

        $scan->device_name = $info->getDeviceName();

        $scan->device_brand = $info->getDeviceBrand();

        $scan->device_model = $info->getDeviceModel();

        $scan->os_name = $info->getOSName();

        $scan->os_version = $info->getOSVersion();

        $scan->client_type = $info->getClientType();

        $scan->client_name = $info->getClientName();

        $scan->client_version = $info->getClientVersion();

        $scan->browser = $info->getBrowser();

        $resolver = new MaxMindResolver();

        $location = $resolver->resolve($scan->ip_address);

        if ($location) {
            $scan->fillLocationData($location);
        }

        $scan->calculateHour();

        if (!empty($overrides)) {
            $scan->forceFill($overrides);
        }

        $this->removeUserIPIfNeeded($scan);

        $scan->save();

        $this::$recentlyCreatedScanRecord = $scan;

        return $scan;
    }

    private function removeUserIPIfNeeded($scan)
    {
        if (Config::get('privacy.store-user-ip-in-scan-record') !== 'disabled') {
            return;
        }

        $scan->ip_address = '';
    }

    public function collectScanDetails(QRCode $qrcode, Request $request)
    {
        $scan = $this->saveScanDetails($qrcode->id, $request->ip());

        $this->increaseQRCodeScan($qrcode);

        $this->increaseScans($this->users->getClientUser($qrcode));

        $this->increaseGlobalScans();

        return $scan;
    }

    public function getScansByUser(User $user)
    {
        $user = $this->users->getParentUser($user);

        $count = $user->getMeta($this->userScanMetaKey());

        if ($count === null) {
            $count = $this->syncUserScanCount($user);
        }

        return $count;
    }

    private function syncUserScanCount(User $user)
    {
        $user = $this->users->getParentUser($user);

        $count = $this->countScansOfUserQRCodes($user);

        $user->setMeta($this->userScanMetaKey(), $count);

        return $count;
    }

    public function increaseScans(User $user)
    {
        $user = $this->users->getParentUser($user);

        $number = $this->getScansByUser($user);

        if (empty($number)) {
            $number = 0;
        }

        $user->setMeta($this->userScanMetaKey(), ++$number);

        return $number;
    }

    public function resetAllUsersScans()
    {
        User::get()->each(
            function (User $user) {
                $this->resetUserScans($user);
            }
        );
    }

    public function resetUserScans(User $user)
    {
        $user = $this->users->getParentUser($user);

        $user->setMeta($this->userScanMetaKey(), 0);
    }

    private function userScanMetaKey()
    {
        return 'number_of_scans';
    }

    public function getLanguageCollectionUrl()
    {
        if (!static::$recentlyCreatedScanRecord) {
            return '';
        }

        $url = url(
            sprintf(
                '/dynamic-style/%s?language=LANGUAGE&signature=%s',
                $this::getRecentlyCreatedScanRecord()?->id,
                $this->getLanguageCollectionSignature()
            )
        );

        return $url;
    }

    public function isLanguageCollectionSignatureValid(QRCodeScanModel $scan, $signature)
    {
        $expected = $this->getLanguageCollectionSignature($scan);

        return $expected === $signature;
    }

    protected function getLanguageCollectionSignature(?QRCodeScanModel $scan = null)
    {
        $scan = $scan ?? $this::getRecentlyCreatedScanRecord();

        $qrcodeId = $scan->qrcode_redirect?->qrcode_id;

        $scanId = $scan->id;

        return sha1($qrcodeId . $scanId . config('app.key'));
    }

    public function resetQRCodeScans(QRCode $qrcode)
    {
        $qrcode->scans_count = 0;

        $qrcode->save();

        QRCodeScanModel::where('qrcode_id', $qrcode->id)->delete();

        PluginManager::doAction(
            PluginManager::ACTION_AFTER_QRCODE_RESET,
            $qrcode
        );
    }
}
