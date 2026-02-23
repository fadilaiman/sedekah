<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class DaunPostController extends Controller
{
    // GET /api/v1/daun/posts
    public function index(): JsonResponse
    {
        if (! config('services.daun.enabled') || ! config('services.daun.api_key')) {
            return response()->json(['data' => []]);
        }

        $posts = Cache::remember('daun_posts_feed', 600, function () {
            try {
                $response = Http::timeout(5)
                    ->withHeaders([
                        'X-API-Key' => config('services.daun.api_key'),
                    ])
                    ->get(config('services.daun.api_url') . '/api/v2/posts', [
                        'username' => 'sedekahinfo',
                        'limit' => 5,
                    ]);

                if (! $response->successful()) {
                    return [];
                }

                $items = $response->json('data', []);

                return collect($items)->map(fn ($post) => [
                    'id'        => $post['id'] ?? null,
                    'content'   => $post['content'] ?? '',
                    'type'      => $post['type'] ?? 'post',
                    'createdAt' => $post['createdAt'] ?? $post['created_at'] ?? null,
                    'likes'     => $post['counts']['likes'] ?? $post['likes_count'] ?? 0,
                    'replies'   => $post['counts']['replies'] ?? $post['replies_count'] ?? 0,
                ])->values()->all();
            } catch (\Throwable $e) {
                report($e);
                return [];
            }
        });

        return response()->json(['data' => $posts]);
    }
}
