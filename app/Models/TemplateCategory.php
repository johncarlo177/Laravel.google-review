<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * @property string name
 * @property string text_color
 * @property int image_id
 * @property int sort_order
 */
class TemplateCategory extends Model
{
    use HasFactory;

    public function getImageUrl()
    {
        return file_url($this->image_id);
    }

    public function toResponse()
    {
        return array_merge(
            $this->toArray(),
            [
                'image_url' => $this->getImageUrl()
            ]
        );
    }
}
