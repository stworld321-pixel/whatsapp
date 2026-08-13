import { Link } from '@inertiajs/react';
import SeoHead from '@/Components/SeoHead';

export default function BlogShow({ post }) {
    return (
        <div className="min-h-screen bg-white dark:bg-neutral-950">
            <SeoHead title={post.meta_title ?? post.title} description={post.meta_description ?? ''} image={post.image_url ?? undefined} />

            <main className="mx-auto max-w-4xl px-4 py-16">
                <Link href={route('blog.index')} className="text-sm font-medium text-brand-600 hover:underline dark:text-brand-400">
                    ← Back to blog
                </Link>

                <div className="mt-6 flex items-center gap-3 text-sm text-neutral-500 dark:text-neutral-400">
                    <span className="rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700 dark:bg-brand-900/20 dark:text-brand-300">
                        {post.category || 'General'}
                    </span>
                    <span>{post.created_at}</span>
                </div>

                <h1 className="mt-4 text-4xl font-bold tracking-tight text-neutral-900 dark:text-white">{post.title}</h1>

                {post.image_url && (
                    <img src={post.image_url} alt={post.title} className="mt-8 h-80 w-full rounded-3xl object-cover shadow-sm" />
                )}

                <article
                    className="prose mt-10 max-w-none dark:prose-invert prose-headings:text-neutral-900 dark:prose-headings:text-white prose-a:text-brand-600 dark:prose-a:text-brand-400"
                    dangerouslySetInnerHTML={{ __html: post.content ?? '' }}
                />
            </main>
        </div>
    );
}
