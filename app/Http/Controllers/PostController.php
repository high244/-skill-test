<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class PostController extends Controller
{
    public function index(Request $request): Response
    {
        $posts = Post::query()
            ->with(['user:id,name,email'])
            ->published()
            ->latest('published_at')
            ->paginate(20)
            ->withQueryString();

        /** @var array<string, mixed> $postsPayload */
        $postsPayload = PostResource::collection($posts)->response()->getData(true);

        if ($request->header('X-Inertia')) {
            return Inertia::render('posts/index', [
                'posts' => $postsPayload,
            ])->toResponse($request);
        }

        return response()->json([
            'posts' => $postsPayload,
        ]);
    }

    public function create(Request $request): Response
    {
        if ($request->header('X-Inertia')) {
            return Inertia::render('posts/create')->toResponse($request);
        }

        return response('posts.create');
    }

    public function store(StorePostRequest $request): RedirectResponse
    {
        $data = $this->normalizeDraftFields($request->validated());

        $post = $request->user()->posts()->create($data);

        return redirect()->route('posts.show', $post);
    }

    public function show(Request $request, Post $post): Response
    {
        if (! $post->isPublished()) {
            abort(404);
        }

        $post->load(['user:id,name,email']);

        if ($request->header('X-Inertia')) {
            return Inertia::render('posts/show', [
                'post' => (new PostResource($post))->toArray($request),
            ])->toResponse($request);
        }

        return response()->json([
            'post' => (new PostResource($post))->toArray($request),
        ]);
    }

    public function edit(Request $request, Post $post): Response
    {
        $this->authorize('update', $post);

        if ($request->header('X-Inertia')) {
            return Inertia::render('posts/edit', [
                'post' => (new PostResource($post->load(['user:id,name,email'])))->toArray($request),
            ])->toResponse($request);
        }

        return response('posts.edit');
    }

    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        $data = $this->normalizeDraftFields($request->validated());

        $post->update($data);

        return redirect()->route('posts.show', $post);
    }

    public function destroy(Post $post): RedirectResponse
    {
        $this->authorize('delete', $post);

        $post->delete();

        return redirect()->route('posts.index');
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizeDraftFields(array $data): array
    {
        if (($data['is_draft'] ?? null) === true) {
            $data['published_at'] = null;
        }

        if (! array_key_exists('is_draft', $data) && ($data['published_at'] ?? null) !== null) {
            $data['is_draft'] = false;
        }

        return $data;
    }
}
