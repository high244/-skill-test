<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    public function index(Request $request): Response
    {
        $posts = $request->user()
            ->posts()
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('posts/index', [
            'posts' => $posts,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Post::class);

        return Inertia::render('posts/create');
    }

    public function store(StorePostRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Normalize draft behavior.
        if (($data['is_draft'] ?? false) === true) {
            $data['published_at'] = null;
        }

        $post = $request->user()->posts()->create($data);

        return redirect()->route('posts.show', $post);
    }

    public function show(Post $post): Response
    {
        $this->authorize('view', $post);

        return Inertia::render('posts/show', [
            'post' => $post,
        ]);
    }

    public function edit(Post $post): Response
    {
        $this->authorize('update', $post);

        return Inertia::render('posts/edit', [
            'post' => $post,
        ]);
    }

    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        $this->authorize('update', $post);

        $data = $request->validated();

        if (($data['is_draft'] ?? false) === true) {
            $data['published_at'] = null;
        }

        $post->update($data);

        return redirect()->route('posts.show', $post);
    }

    public function destroy(Request $request, Post $post): RedirectResponse
    {
        $this->authorize('delete', $post);

        $post->delete();

        return redirect()->route('posts.index');
    }
}
