<?php

namespace App\Support;

use App\Models\Folder;
use App\Models\QRCode;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class FolderManager
{
    public function list(User $user)
    {
        if ($user->is_sub) {
            return $this->getSubuserFolders($user)->map(
                fn($f) => $this->folderResponse($f)
            );
        }

        return Folder::where(
            'user_id',
            $user->id
        )
            ->get()
            ->map(
                fn($f) => $this->folderResponse($f)
            );
    }

    protected function folderResponse(Folder $folder)
    {
        $folder->qrcode_count = $folder->qrcodes()->where('is_template', '<>', 1)->count();

        return $folder;
    }

    public function addQRCode(QRCode $qrcode, Folder $folder)
    {
        $qrcode->folder_id = $folder->id;

        $qrcode->save();

        $folder->refresh();
    }

    public function getOrCreateFolderByFolderName(User $user, $folderName): Folder
    {
        /**
         * @var Folder
         */
        $folder = Folder::where([
            'user_id' => $user->id,
            'name' => $folderName
        ])->first();

        if (!$folder) {
            $folder = $this->saveFolder($user, $folderName);
        }

        return $folder;
    }

    public function saveFolder(User $user, $folderName, $folderId = null)
    {
        $folder = Folder::find($folderId);

        if (!$folder) {
            $folder = new Folder;
        }

        $folder->name = $folderName;

        $folder->user_id = $user->id;

        $folder->save();

        return $folder;
    }

    public function resetQRCodeFolder(QRCode $qrcode)
    {
        $qrcode->folder_id = null;

        $qrcode->save();
    }

    public function delete(Folder $folder)
    {
        $folder->delete();

        return $folder;
    }

    public function getSubuserFolders(User $user)
    {
        $ids = DB::table('subuser_folders')
            ->select('folder_id')
            ->where('user_id', $user->id)
            ->pluck('folder_id');

        return Folder::whereIn('id', $ids)->get();
    }

    public function grantSubuserAccess(User $user, Folder $folder)
    {
        return DB::table('subuser_folders')->insert([
            'user_id' => $user->id,
            'folder_id' => $folder->id
        ]);
    }

    public function revokSubuserAccess(User $user, Folder $folder)
    {
        return DB::table('subuser_folders')
            ->where('user_id', $user->id)
            ->where('folder_id', $folder->id)
            ->delete();
    }
}
