<?php

namespace App\Http\Middleware;

use App\Models\Category;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
                'status' => session('status'),
            ],
            'pendingSubmissions' => $request->user()?->isAdmin()
                ? Submission::where('status', 'pending')->count()
                : 0,
            'categories' => Cache::remember('inertia_categories', 3600, fn () =>
                Category::active()->ordered()->get()
                    ->map(fn ($c) => [
                        'id'     => $c->id,
                        'value'  => $c->value,
                        'label'  => $c->label,
                        'icon'   => $c->icon,
                        'color'  => $c->color,
                        'order'  => $c->order,
                        'active' => $c->active,
                    ])->values()->all()
            ),
        ];
    }
}
