import { Link } from '@inertiajs/react';
import SeoHead from '@/Components/SeoHead';

export default function BlogIndex({ posts = [] }) {
    return (
        <div className="min-h-screen bg-white dark:bg-neutral-950">
            <SeoHead title="Blog" description="Latest guides, updates, and product news." />

            <section className="bg-gradient-to-br from-[#3ba6e8] via-[#2f8fd6] to-[#1f6eb3] text-white">
                <div className="mx-auto max-w-6xl px-4 py-20 text-center">
                    <p className="text-sm font-semibold uppercase tracking-[0.25em] text-white/80">Blog</p>
                    <h1 className="mt-4 text-4xl font-bold tracking-tight sm:text-5xl">Updates, guides, and ideas</h1>
                    <p className="mx-auto mt-5 max-w-2xl text-base text-white/85">
                        Read product updates, how-to guides, and notes from the team.
                    </p>
                </div>
            </section>

            <main className="mx-auto max-w-6xl px-4 py-16">
                <div className="grid gap-8 md:grid-cols-2 xl:grid-cols-3">
                    {posts.map((post) => (
                        <article key={post.slug} className="overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg dark:border-neutral-800 dark:bg-neutral-900">
                            {post.image_url ? (
                                <Link href={route('blog.show', post.slug)}>
                                    <img src={post.image_url} alt={post.title} className="h-52 w-full object-cover" />
                                </Link>
                            ) : (
                                <div className="h-52 w-full bg-gradient-to-br from-brand-100 to-brand-200 dark:from-brand-900/30 dark:to-brand-800/30" />
                            )}
                            <div className="space-y-4 p-6">
                                <div className="flex items-center justify-between gap-3 text-xs text-neutral-500 dark:text-neutral-400">
                                    <span className="rounded-full bg-brand-50 px-3 py-1 font-medium text-brand-700 dark:bg-brand-900/20 dark:text-brand-300">
                                        {post.category || 'General'}
                                    </span>
                                    <span>{post.created_at}</span>
                                </div>
                                <h2 className="text-xl font-semibold text-neutral-900 dark:text-white">{post.title}</h2>
                                <p className="text-sm text-neutral-600 dark:text-neutral-400">
                                    {post.meta_description || 'Open the post to read more.'}
                                </p>
                                <Link href={route('blog.show', post.slug)} className="inline-flex text-sm font-semibold text-brand-600 hover:underline dark:text-brand-400">
                                    Read more →
                                </Link>
                            </div>
                        </article>
                    ))}
                </div>

                {!posts.length && (
                    <div className="rounded-2xl border border-dashed border-neutral-300 p-10 text-center text-neutral-500 dark:border-neutral-700 dark:text-neutral-400">
                        No blog posts published yet.
                    </div>
                )}
            </main>
        </div>
    );
}
