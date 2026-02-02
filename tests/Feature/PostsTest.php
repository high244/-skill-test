<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_on_posts_index(): void
    {
        $this->get('/posts')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_posts_index(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/posts')
            ->assertOk();
    }

    public function test_user_can_create_a_draft_post(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/posts', [
            'title' => 'My Draft',
            'content' => 'Hello',
            'is_draft' => true,
        ]);

        $post = Post::query()->where('user_id', $user->id)->first();

        $response->assertRedirect('/posts/'.$post->id);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'user_id' => $user->id,
            'title' => 'My Draft',
            'is_draft' => 1,
            'published_at' => null,
        ]);
    }

    public function test_user_cannot_view_another_users_post(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $post = Post::factory()->for($otherUser)->create();

        $this->actingAs($user)
            ->get('/posts/'.$post->id)
            ->assertForbidden();
    }

    public function test_user_can_update_own_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->draft()->create([
            'title' => 'Old',
            'content' => 'Old content',
        ]);

        $response = $this->actingAs($user)->put('/posts/'.$post->id, [
            'title' => 'New',
            'content' => 'New content',
            'is_draft' => false,
            'published_at' => now()->toISOString(),
        ]);

        $response->assertRedirect('/posts/'.$post->id);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'user_id' => $user->id,
            'title' => 'New',
            'content' => 'New content',
            'is_draft' => 0,
        ]);
    }

    public function test_user_cannot_update_another_users_post(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $post = Post::factory()->for($otherUser)->create();

        $this->actingAs($user)
            ->put('/posts/'.$post->id, [
                'title' => 'Hacked',
                'content' => 'Hacked',
                'is_draft' => true,
            ])
            ->assertForbidden();
    }

    public function test_user_can_delete_own_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();

        $this->actingAs($user)
            ->delete('/posts/'.$post->id)
            ->assertRedirect('/posts');

        $this->assertDatabaseMissing('posts', [
            'id' => $post->id,
        ]);
    }

    public function test_user_cannot_delete_another_users_post(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $post = Post::factory()->for($otherUser)->create();

        $this->actingAs($user)
            ->delete('/posts/'.$post->id)
            ->assertForbidden();

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
        ]);
    }
}
