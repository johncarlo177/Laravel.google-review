<?php

namespace App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks;

use App\Interfaces\FileManager;
use App\Models\File;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Support\Str;

class BlockModel
{
    use WriteLogs;
    private $id, $mode, $slug, $data;

    private FileManager $files;

    public function __construct($blockData)
    {
        $this->setId(@$blockData['id']);
        $this->setMode(@$blockData['mode']);
        $this->setSlug(@$blockData['slug']);
        $this->setData(@$blockData['data']);

        $this->files = app(FileManager::class);
    }

    public function setId($id)
    {
        if (empty($id)) {
            $this->id = 'block-' . Str::uuid();
        } else {
            $this->id = $id;
        }
    }

    public function getId()
    {
        return $this->id;
    }

    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    public function getMode()
    {
        return $this->mode;
    }

    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function getData()
    {
        return $this->data;
    }

    /**
     * Get data field
     * @return mixed
     */
    public function field($key, $default = null)
    {
        $value = @$this->getData()[$key];

        if (empty($value)) return $default;

        return $value;
    }

    public function fileUrl($key, $default = null)
    {
        $id = $this->field($key);

        return $this->fileUrlById($id, $default);
    }

    public function fileUrlById($fileId, $default = null)
    {
        if (empty($fileId)) return $default;

        $file = File::find($fileId);

        if (!$file) return $default;

        if (!$this->files->exists($file)) return $default;

        $url = $this->files->url($file);

        return $url;
    }

    public function empty($key)
    {
        return empty($this->field($key));
    }

    public function notEmpty($key)
    {
        return !$this->empty($key);
    }

    public function equals($key, $value)
    {
        return $this->field($key) == $value;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'mode' => $this->mode,
            'slug' => $this->slug,
            'data' => $this->data,
        ];
    }

    public function setData($data)
    {
        if (empty($data)) {
            $data = [];
        }

        $this->data = $data;
    }

    public function getSortOrder()
    {
        $s = @$this->data['sortOrder'];

        if ($s === null) {
            return 100;
        }

        return $s;
    }

    public function setSortOrder($sort)
    {
        $this->setField('sortOrder', $sort);
    }

    public function setField($field, $value)
    {
        if (!is_array($this->data)) {
            return;
        }

        $this->data[$field] = $value;
    }
}
