<?php

namespace App\Support;

use App\Events\ShouldSaveQRCodeVariants;
use App\Interfaces\FileManager;
use App\Interfaces\UserManager;
use App\Models\LeadForm;
use App\Models\QRCode;
use App\Models\QRCodeRedirect;
use App\Models\QRCodeScan;
use App\Models\User;
use App\Support\QRCodeTypes\QRCodeTypeManager;
use App\Support\System\Traits\WriteLogs;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Illuminate\Support\Collection;

class QRCodeManager
{
    use WriteLogs;

    private UserManager $users;

    private FileManager $files;

    private QRCodeWebPageDesignManager $webpageDesignManager;

    private FolderManager $folders;

    private LeadFormManager $leadForms;

    private QRCodeTypeManager $types;

    public function __construct()
    {
        $this->users = app(UserManager::class);

        $this->files = app(FileManager::class);

        $this->webpageDesignManager = app(QRCodeWebPageDesignManager::class);

        $this->folders = app(FolderManager::class);

        $this->leadForms = app(LeadFormManager::class);

        $this->types = app(QRCodeTypeManager::class);
    }

    public function createdBySubUser(QRCode $qrcode, User $user)
    {
        $found = $user->sub_users->pluck('id')
            ->first(fn($id) => $id == $qrcode->user_id);

        return !empty($found);
    }

    /**
     * @return QRCode[]
     */
    public function copy(QRCode $qrcode, $count = 1): array
    {
        $result = [];

        for ($i = 0; $i < $count; $i++) {
            $copier = new QRCodeCopier();

            $result[] = $copier->copy($qrcode);
        }

        return $result;
    }

    public function changeType(QRCode $qrcode, $newType)
    {
        if (!$this->types->find($newType)) {
            throw new Exception(t('Invalid type'));
        }

        $qrcode->type = $newType;

        $qrcode->save();

        return $qrcode;
    }

    /**
     * @param array|Collection $qrcodeIds
     */
    public function deleteMany($qrcodeIds)
    {

        $this->deleteQRCodesFiles($qrcodeIds);

        $this->leadForms->deleteQRCodesForms($qrcodeIds);

        $redirectIds = QRCodeRedirect::whereIn('qrcode_id', $qrcodeIds)->pluck('id');

        $scanIds = QRCodeScan::whereIn('qrcode_id', $qrcodeIds)
            ->orWhereIn('qrcode_redirect_id', $redirectIds)
            ->pluck('id');

        DB::table('qrcode_scans')->whereIn('id', $scanIds)->delete();

        DB::table('qrcode_redirects')->whereIn('id', $redirectIds)->delete();

        DB::table('qrcode_webpage_designs')->whereIn('qrcode_id', $qrcodeIds)->delete();

        DB::table('qrcodes')->whereIn('id', $qrcodeIds)->delete();
    }

    public function delete(QRCode $qrcode)
    {
        return $this->deleteMany([$qrcode->id]);
    }

    /** 
     * @param User actor the user who is requesting the qr code  
     * @param User qrcodeMaker count qr codes which is created by spcified qrcodeMaker, can be null if the actor is permitted to qrcode.list-all.
     * @param string|array qrcodeType qrcode type to search for, null means all qrcodes would be counted.
     **/
    public function getQRCodeCount(
        User $actor,
        User $qrcodeMaker = null,
        $qrcodeType = null
    ) {
        $query = $this->buildUserSearchQuery($actor, $qrcodeMaker);

        $query->where('is_template', false);

        if (!empty($qrcodeType) && is_string($qrcodeType)) {
            $query->where('type', $qrcodeType);
        }

        if (!empty($qrcodeType) && is_array($qrcodeType)) {
            $query->whereIn('type', $qrcodeType);
        }

        return $query->count();
    }

    public function buildUserSearchQuery(User $actor, User $qrcodeMaker = null)
    {
        $query = QRCode::query();

        $this->applyAdminSearchQuery($query, $actor, $qrcodeMaker);

        $this->applyOwnerSearchQuery($query, $actor, $qrcodeMaker);

        return $query;
    }

    private function applyOwnerSearchQuery(Builder $query, User $actor, User $qrcodeMaker = null)
    {
        if ($actor->permitted('qrcode.list-all')) return;

        if (!$qrcodeMaker) {
            $qrcodeMaker = $actor;
        }

        if ($qrcodeMaker->id != $actor->id) {
            throw new InvalidArgumentException('User does not have the ability to qrcode.list-all and is trying to count QR Codes that are made by another user.');
        }

        $ids = $this->users->getUserIdsOnTheSameSubscription($qrcodeMaker);

        $query->whereIn('user_id', $ids);
    }

    private function applyAdminSearchQuery(Builder $query, User $actor, User $qrcodeMaker = null)
    {
        if (!$actor->permitted('qrcode.list-all')) return;

        if (!$qrcodeMaker) return;

        $ids = $this->users->getUserIdsOnTheSameSubscription($qrcodeMaker);

        $query->whereIn('user_id', $ids);
    }

    public function archive(QRCode $qrcode, $archived)
    {
        $qrcode->archived = $archived;

        if ($archived) {
            $qrcode->folder_id = null;
        }

        if ($archived) {
            $qrcode->archived_at = now();
        }

        $qrcode->save();

        return $qrcode;
    }

    public function deleteRecentlyDeletedAutomatically()
    {
        $ids = QRCode::where(
            'archived_at',
            '<=',
            now()->subDays(30)
        )
            ->pluck('id');

        $this->deleteMany($ids);
    }

    public function deleteAllRecentlyDeletedQRCodes(User $user)
    {
        $query = QRCode::where(
            'archived',
            true
        );

        if (!$user->permitted('qrcode.destroy-any')) {
            $query->where('user_id', $user->id);
        }

        $ids = $query->pluck('id');

        $this->deleteMany($ids);
    }

    public function changeUser(QRCode $qrcode, $userId)
    {
        $qrcode->user_id = $userId;

        $qrcode->save();

        $files = $this->getQRCodeFiles($qrcode);

        $files->each(function ($file) use ($userId) {
            $file->user_id = $userId;
            $file->save();
        });

        $design = $this->webpageDesignManager->getDesign($qrcode);

        $leadFormId = @$design->design['lead_form_id'];

        if ($leadFormId) {

            $leadForm = LeadForm::find($leadFormId);

            $leadForm->user_id = $userId;

            $leadForm->save();
            //
        }

        $this->folders->resetQRCodeFolder($qrcode);

        return $qrcode;
    }

    private function getQRCodeFiles(QRCode $qrcode)
    {
        return collect(QRCodeAttachedFiles::withQRCode($qrcode)->getFiles());
    }

    private function deleteQRCodesFiles($qrcodeIds)
    {
        $qrcodes = QRCode::whereIn('id', $qrcodeIds);

        $qrcodes->each(function (QRCode $qrcode) {
            QRCodeAttachedFiles::withQRCode($qrcode)
                ->deleteFiles();
        });
    }

    private function changePinCode(QRCode $qrcode, $pincode = null)
    {
        $qrcode->pincode = $pincode;

        $qrcode->save();

        $this->regenerateQRCodeSVGFile($qrcode);

        return [
            'success' => true
        ];
    }

    private function resetPinCode(QRCode $qrcode)
    {
        return $this->changePinCode($qrcode);
    }

    private function regenerateQRCodeSVGFile(QRCode $qrcode)
    {
        event(new ShouldSaveQRCodeVariants($qrcode));
    }

    public function setPincode(QRCode $qrcode, $pincode)
    {
        if (empty($pincode)) {
            return $this->resetPinCode($qrcode);
        }

        $length = config('qrcode.pincode_length') ?? 5;

        $isNumeric = config('qrcode.pincode_type') !== 'any';

        if ($isNumeric && !preg_match(
            sprintf('#^\d{%s}$#', $length),
            $pincode
        )) {
            return [
                'success' => false,
                'message' => t('PIN Code must be 5 digits')
            ];
        }

        return $this->changePinCode($qrcode, $pincode);
    }
}
