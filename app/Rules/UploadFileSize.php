<?php

namespace App\Rules;

use App\Interfaces\UserManager;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Support\System\Traits\WriteLogs;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UploadFileSize implements ValidationRule
{
    use WriteLogs;

    private ?SubscriptionPlan $plan = null;
    private UserManager $users;
    private ?User $user = null;

    public function __construct()
    {
        $this->users = app(UserManager::class);

        $this->user = request()->user();

        if ($this->user)
            $this->plan = $this->users->getCurrentPlan(request()->user());
    }

    private function getFileSizeLimit()
    {
        if (!$this->user) {
            return -1;
        }

        if ($this->user->isSuperAdmin()) {
            return -1;
        }

        if (!$this->plan) {
            return -1;
        }

        return $this->plan->file_size_limit;
    }

    protected function getActualMaximumUploadFileSizeInMb()
    {
        return round($this->getActualMaximumUploadFileSize() / (1024 * 1024));
    }

    // Returns the file size limit in bytes
    protected function getActualMaximumUploadFileSize()
    {
        static $max_size = -1;

        if ($max_size < 0) {
            // Start with post_max_size.
            $post_max_size = $this->parseSize(ini_get('post_max_size'));
            if ($post_max_size > 0) {
                $max_size = $post_max_size;
            }

            // If upload_max_size is less, then reduce. Except if upload_max_size is
            // zero, which indicates no limit.
            $upload_max = $this->parseSize(ini_get('upload_max_filesize'));
            if ($upload_max > 0 && $upload_max < $max_size) {
                $max_size = $upload_max;
            }
        }
        return $max_size;
    }

    protected function parseSize($size)
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
        $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
        if ($unit) {
            // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        } else {
            return round($size);
        }
    }

    public function getAllowedFileSize()
    {
        $size = $this->getFileSizeLimit();

        if ($size == -1) {
            return $this->getActualMaximumUploadFileSizeInMb() . ' ' . t('mb');
        }

        $size = min($size, $this->getActualMaximumUploadFileSizeInMb());

        return sprintf(
            '%s %s',
            $size,
            t('mb')
        );
    }

    private function shouldRun()
    {
        return $this->getFileSizeLimit() > -1;
    }

    private function fileSizeLimitReached($size)
    {
        $limitSize = $this->getFileSizeLimit();

        if ($limitSize == -1) return false;

        return $this->mb($size) > $limitSize;
    }

    private function mb($bytes)
    {
        return $bytes / (1024 * 1024);
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->shouldRun()) {
            return;
        }

        $size = $value->getSize();

        if ($this->fileSizeLimitReached($size)) {
            $fail(t('Maximum file size limit reached, upgrade to higher plan to upload larger files.'));
        }
    }
}
