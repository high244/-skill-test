import AppLayout from '@/layouts/app-layout';
import Heading from '@/components/heading';
import { Head, Link } from '@inertiajs/react';

type Post = {
    id: number;
    title: string;
    is_draft: boolean;
    published_at: string | null;
};

type Pagination<T> = {
    data: T[];
};

export default function PostsIndex({ posts }: { posts: Pagination<Post> }) {
    return (
        <AppLayout>
            <Head title="Posts" />
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <Heading title="Posts" description="Your posts" />
                    <Link className="text-sm underline" href={route('posts.create')}>
                        New post
                    </Link>
                </div>

                <div className="rounded border">
                    <ul className="divide-y">
                        {posts.data.map((post) => (
                            <li key={post.id} className="p-4">
                                <Link className="font-medium underline" href={route('posts.show', post.id)}>
                                    {post.title}
                                </Link>
                                <div className="text-xs text-neutral-500">
                                    {post.is_draft ? 'Draft' : post.published_at ? `Publish at: ${post.published_at}` : '—'}
                                </div>
                            </li>
                        ))}
                    </ul>
                </div>
            </div>
        </AppLayout>
    );
}
