<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $articleId = $this->route('article');
        
        return [
            'title' => 'sometimes|required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:articles,slug,' . $articleId,
            'excerpt' => 'nullable|string|max:500',
            'content' => 'sometimes|required|string',
            'banner_image' => 'nullable|url|max:500',
            'status' => 'sometimes|required|in:draft,published,archived',
            'published_at' => 'nullable|date',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|array',
        ];
    }
}