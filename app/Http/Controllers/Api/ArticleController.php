<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ArticleController extends Controller
{
    /**
     * adnim
     */
    public function index(Request $request): JsonResponse
    {
        $query = Article::query();
 
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
 
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

  
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);
 
        $perPage = $request->input('per_page', 15);
        $articles = $query->paginate($perPage);

        return response()->json($articles);
    }

    public function store(StoreArticleRequest $request): JsonResponse
    {
        $data = $request->validated();
 
        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $article = Article::create($data);

        return response()->json([
            'message' => 'Article created successfully',
            'data' => $article
        ], 201);
    }


    public function show(string $identifier): JsonResponse
    {
         $article = is_numeric($identifier)
            ? Article::findOrFail($identifier)
            : Article::where('slug', $identifier)->firstOrFail();

        return response()->json($article);
    }


    public function update(UpdateArticleRequest $request, Article $article): JsonResponse
    {
        $data = $request->validated();

        if (isset($data['status']) && $data['status'] === 'published' && !$article->published_at) {
            $data['published_at'] = $data['published_at'] ?? now();
        }

        $article->update($data);

        return response()->json([
            'message' => 'Article updated successfully',
            'data' => $article
        ]);
    }

    public function destroy(Article $article): JsonResponse
    {
        $article->delete(); 

        return response()->json([
            'message' => 'Article deleted successfully'
        ]);
    }
    
    /**
     * public
     */

    public function published(Request $request): JsonResponse
    {
        $query = Article::published();

        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        $query->orderBy('published_at', 'desc');

        $perPage = $request->input('per_page', 12);
        $articles = $query->paginate($perPage);

        return response()->json($articles);
    }

    public function showPublished(string $slug): JsonResponse
    {
        $article = Article::published()
            ->where('slug', $slug)
            ->firstOrFail();

        $article->incrementViewCount();

        return response()->json($article);
    }
}