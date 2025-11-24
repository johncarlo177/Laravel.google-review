<?php

namespace App\Models\Traits;

use App\Models\MetaItem;

trait HasMetaFields
{
    public static function queryMetaField($key, $value)
    {
        $items = MetaItem::where('key', $key)
            ->where('related_model', static::class)
            ->where('value', json_encode($value))
            ->get();

        $ids = $items->pluck('related_model_id');

        return static::whereIn('id', $ids)->get();
    }

    public static function byMetaField($key, $value): ?static
    {
        $item = MetaItem::where('key', $key)
            ->where('related_model', static::class)
            ->where('value', json_encode($value))
            ->first();


        if (!$item) {
            return null;
        }

        return static::find($item->related_model_id);
    }



    public function setMeta($key, $value)
    {
        $meta = $this->getMetaRecord($key);

        if (!$meta) {
            $meta = new MetaItem();
        }

        $meta->key = $key;

        $meta->value = json_encode($value);

        $meta->related_model = static::class;

        $meta->related_model_id = $this->id;

        $meta->save();

        return $meta;
    }

    public function getMeta($key)
    {
        $meta = $this->getMetaRecord($key);

        if (!$meta) {
            return null;
        }

        return json_decode($meta->value);
    }

    public function removeMeta($key)
    {
        $this->getMetaRecord($key)?->delete();
    }

    private function getMetaRecord($key)
    {
        return MetaItem::where('key', $key)
            ->where('related_model', static::class)
            ->where('related_model_id', $this->id)
            ->first();
    }
}
