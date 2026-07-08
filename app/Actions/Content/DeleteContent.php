<?php

namespace App\Actions\Content;

use App\Models\Content;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DeleteContent
{
    public function execute(Content $content): void
    {
        DB::transaction(function () use ($content) {
            // Lepas dari roadmap dulu (morph tanpa FK cascade ke contents);
            // user_progress ikut terhapus via FK cascade pada roadmap_item_id
            $content->roadmapItems()->delete();

            if ($content->file_path) {
                Storage::disk('public')->delete($content->file_path);
            }

            $content->delete();
        });
    }
}
