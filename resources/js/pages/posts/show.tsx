import AppLayout from '@/layouts/app-layout';
import Heading from '@/components/heading';
import { Head, Link } from '@inertiajs/react';

type Post = {
    id: number;
    title: string;
    content: string;
    is_draft: boolean;
    published_at: string | null;
};

export default function PostsShow({ post }: { post: Post }) {
    return (
        <AppLayout>
            <Head title={post.title} />
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <Heading title={post.title} description={post.is_draft ? 'Draft' : post.published_at ?? '—'} />
                    <Link className="text-sm underline" href={route('posts.edit', post.id)}>
                        Edit
                    </Link>
                </div>

                <div className="rounded border p-4 whitespace-pre-wrap">{post.content}</div>

                <Link className="text-sm underline" href={route('posts.index')}>
                    Back
                </Link>
            </div>
        </AppLayout>
    );
}
