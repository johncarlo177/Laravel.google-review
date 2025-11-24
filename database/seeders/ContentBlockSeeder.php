<?php

namespace Database\Seeders;

use App\Models\ContentBlock;
use Illuminate\Database\Seeder;

class ContentBlockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = require_once(base_path('database/raw/content-blocks.php'));

        foreach ($data as $row) {
            $post = new ContentBlock();

            $row_data = array_merge($row, ['id' => null]);

            $post->forceFill($row_data);

            $post->save();
        }
    }
}
