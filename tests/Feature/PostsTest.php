<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostsTest extends TestCase
{
    use RefreshDatabase;

    public function test_posts_index_is_public_and_returns_json(): void
    {
        $this->get('/posts')
            ->assertOk()
            ->assertJsonStructure([
                'posts' => [
                    'data',
                    'links',
                    'meta',
                ],
            ]);
    }

    public function test_posts_index_excludes_drafts_and_scheduled_and_includes_author(): void
    {
        $author = User::factory()->create();

        $published = Post::factory()->for($author)->published()->create([
            'title' => 'Published',
        ]);
        Post::factory()->for($author)->draft()->create([
            'title' => 'Draft',
        ]);
        Post::factory()->for($author)->scheduled()->create([
            'title' => 'Scheduled',
        ]);

        $this->get('/posts')
            ->assertOk()
            ->assertJsonFragment(['id' => $published->id])
            ->assertJsonMissing(['title' => 'Draft'])
            ->assertJsonMissing(['title' => 'Scheduled'])
            ->assertJsonFragment(['id' => $author->id]);
    }

    public function test_posts_create_requires_auth_and_returns_string(): void
    {
        $this->get('/posts/create')->assertRedirect('/login');

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/posts/create')
            ->assertOk()
            ->assertSeeText('posts.create');
    }

    public function test_posts_store_requires_auth_and_validates(): void
    {
        $this->post('/posts', [
            'title' => 'x',
            'content' => 'y',
        ])->assertRedirect('/login');

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/posts', [])
            ->assertSessionHasErrors(['title', 'content']);
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

    public function test_posts_show_returns_404_for_draft_or_scheduled_and_json_for_published(): void
    {
        $author = User::factory()->create();
        $draft = Post::factory()->for($author)->draft()->create();
        $scheduled = Post::factory()->for($author)->scheduled()->create();
        $published = Post::factory()->for($author)->published()->create();

        $this->get('/posts/'.$draft->id)->assertNotFound();
        $this->get('/posts/'.$scheduled->id)->assertNotFound();
        $this->get('/posts/'.$published->id)
            ->assertOk()
            ->assertJsonPath('post.id', $published->id)
            ->assertJsonPath('post.user.id', $author->id);
    }

    public function test_posts_edit_is_only_accessible_by_author_and_returns_string(): void
    {
        $author = User::factory()->create();
        $other = User::factory()->create();
        $post = Post::factory()->for($author)->draft()->create();

        $this->actingAs($other)->get('/posts/'.$post->id.'/edit')->assertForbidden();
        $this->actingAs($author)->get('/posts/'.$post->id.'/edit')->assertOk()->assertSeeText('posts.edit');
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
