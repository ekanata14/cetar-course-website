<?php

namespace App\Actions\Content;

use App\DTOs\Content\ContentData;
use App\Models\Content;
use Illuminate\Support\Facades\Storage;

class UpdateContent
{
    public function execute(Content $content, ContentData $data): Content
    {
        // File PDF baru menggantikan yang lama; null = pertahankan file lama
        $filePath = $content->file_path;

        if ($data->type === 'pdf' && $data->filePath) {
            if ($filePath) {
                Storage::disk('public')->delete($filePath);
            }
            $filePath = $data->filePath;
        }

        $content->update([
            'title' => $data->title,
            'type' => $data->type,
            'body' => $data->type === 'text' ? $data->body : null,
            'file_path' => $data->type === 'pdf' ? $filePath : null,
            'video_url' => $data->type === 'video' ? $data->videoUrl : null,
        ]);

        return $content->refresh();
    }
}
