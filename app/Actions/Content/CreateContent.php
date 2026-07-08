<?php

namespace App\Actions\Content;

use App\DTOs\Content\ContentData;
use App\Models\Content;

class CreateContent
{
    public function execute(ContentData $data): Content
    {
        return Content::create([
            'title' => $data->title,
            'type' => $data->type,
            'body' => $data->type === 'text' ? $data->body : null,
            'file_path' => $data->type === 'pdf' ? $data->filePath : null,
            'video_url' => $data->type === 'video' ? $data->videoUrl : null,
        ]);
    }
}
