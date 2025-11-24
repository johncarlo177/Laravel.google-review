<?php

namespace App\Support\QRCodeTypes\BusinessReview;

use App\Models\BusinessReviewFeedback;

class FeedbackSaver
{
    protected $feedback = null,
        $stars = null,
        $name = null,
        $email = null,
        $mobile = null,
        $qrcode_id = null;

    public static function withData($array)
    {
        $instance = new static;

        foreach ($instance as $key => $value) {
            $instance->{$key} = @$array[$key];
        }

        return $instance;
    }

    protected function shouldSave()
    {
        return !empty($this->stars);
    }

    public function save()
    {
        if (!$this->shouldSave()) {
            return;
        }

        $model = new BusinessReviewFeedback();

        $model->feedback = $this->feedback;

        $model->stars = $this->stars;

        $model->qrcode_id = $this->qrcode_id;

        $model->name = $this->name;

        $model->email = $this->email;

        $model->mobile = $this->mobile;

        $model->save();

        return $model;
    }
}
