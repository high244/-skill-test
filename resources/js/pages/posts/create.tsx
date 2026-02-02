import AppLayout from '@/layouts/app-layout';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Head, useForm } from '@inertiajs/react';

export default function PostsCreate() {
    const form = useForm<{
        title: string;
        content: string;
        is_draft: boolean;
        published_at: string | null;
    }>({
        title: '',
        content: '',
        is_draft: true,
        published_at: null as string | null,
    });

    return (
        <AppLayout>
            <Head title="New Post" />
            <div className="space-y-6">
                <Heading title="New Post" description="Create a post" />

                <form
                    className="space-y-4"
                    onSubmit={(e) => {
                        e.preventDefault();
                        form.post(route('posts.store'));
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
                        Save as draft
                    </label>

                    <button className="rounded bg-black px-4 py-2 text-white" disabled={form.processing}>
                        Create
                    </button>
                </form>
            </div>
        </AppLayout>
    );
}
