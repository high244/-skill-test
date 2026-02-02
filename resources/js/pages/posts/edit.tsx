import AppLayout from '@/layouts/app-layout';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Head, useForm } from '@inertiajs/react';

type Post = {
    id: number;
    title: string;
    content: string;
    is_draft: boolean;
    published_at: string | null;
};

export default function PostsEdit({ post }: { post: Post }) {
    const form = useForm({
        title: post.title,
        content: post.content,
        is_draft: post.is_draft,
        published_at: post.published_at,
    });

    return (
        <AppLayout>
            <Head title={`Edit: ${post.title}`} />
            <div className="space-y-6">
                <Heading title="Edit Post" description={post.title} />

                <form
                    className="space-y-4"
                    onSubmit={(e) => {
                        e.preventDefault();
                        form.put(route('posts.update', post.id));
                    }}
                >
                    <div className="space-y-1">
                        <label className="text-sm font-medium">Title</label>
                        <input
                            className="w-full rounded border p-2"
                            value={form.data.title}
                            onChange={(e) => form.setData('title', e.target.value)}
                        />
                        <InputError message={form.errors.title} />
                    </div>

                    <div className="space-y-1">
                        <label className="text-sm font-medium">Content</label>
                        <textarea
                            className="w-full rounded border p-2"
                            rows={6}
                            value={form.data.content}
                            onChange={(e) => form.setData('content', e.target.value)}
                        />
                        <InputError message={form.errors.content} />
                    </div>

                    <label className="flex items-center gap-2 text-sm">
                        <input
                            type="checkbox"
                            checked={form.data.is_draft}
                            onChange={(e) => form.setData('is_draft', e.target.checked)}
                        />
                        Draft
                    </label>

                    <button className="rounded bg-black px-4 py-2 text-white" disabled={form.processing}>
                        Save
                    </button>
                </form>
            </div>
        </AppLayout>
    );
}
