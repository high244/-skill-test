<?php

namespace App\Http\Requests;

use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $post = $this->route('post');

        if (! $post instanceof Post) {
            return false;
        }

        return $user !== null && $user->can('update', $post);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'is_draft' => ['sometimes', 'boolean'],
            'published_at' => ['nullable', 'date'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('is_draft')) {
            $this->merge([
                'is_draft' => filter_var($this->input('is_draft'), FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? false,
            ]);
        }
    }
}
