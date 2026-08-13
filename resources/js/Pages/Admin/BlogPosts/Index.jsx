import { useMemo, useRef, useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import RichTextEditor from '@/Components/RichTextEditor';
import { router, usePage } from '@inertiajs/react';
import { BookOpenText, Pencil, Plus, Trash2, Image as ImageIcon, X } from 'lucide-react';

function slugify(value) {
    return (value ?? '')
        .toString()
        .trim()
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');
}

function PostForm({ post = null, onClose }) {
    const { errors: pageErrors = {} } = usePage().props ?? {};
    const fileRef = useRef(null);
    const [data, setData] = useState({
        slug: post?.slug ?? '',
        title: post?.title ?? '',
        content: post?.content ?? '',
        meta_title: post?.meta_title ?? '',
        meta_description: post?.meta_description ?? '',
        category: post?.category ?? '',
        published: post?.published ?? false,
    });
    const [imageFile, setImageFile] = useState(null);
    const [imagePreview, setImagePreview] = useState(null);
    const [processing, setProcessing] = useState(false);

    const updateField = (key, value) => {
        setData((current) => ({ ...current, [key]: value }));
    };

    const onPickImage = (file) => {
        if (!file) return;
        setImageFile(file);
        setImagePreview(URL.createObjectURL(file));
    };

    const clearImage = () => {
        setImageFile(null);
        setImagePreview(null);
        if (fileRef.current) fileRef.current.value = '';
    };

    const submit = (e) => {
        e.preventDefault();
        setProcessing(true);

        const fd = new FormData();
        fd.append('slug', slugify(data.slug || data.title));
        fd.append('title', data.title ?? '');
        fd.append('content', data.content ?? '');
        fd.append('meta_title', data.meta_title ?? '');
        fd.append('meta_description', data.meta_description ?? '');
        fd.append('category', data.category ?? '');
        fd.append('published', data.published ? '1' : '0');
        if (imageFile) fd.append('image', imageFile);
        if (post) fd.append('_method', 'PUT');

        const options = {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => onClose?.(),
            onFinish: () => setProcessing(false),
        };

        if (post) {
            router.post(route('admin.blog-posts.update', post.id), fd, options);
        } else {
            router.post(route('admin.blog-posts.store'), fd, options);
        }
    };

    const imageSrc = imagePreview || post?.image_url || null;

    return (
        <form onSubmit={submit} className="space-y-5">
            <div className="grid gap-4 md:grid-cols-2">
                <div className="space-y-1">
                    <label className="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Blog title</label>
                    <input
                        type="text"
                        value={data.title}
                        onChange={(e) => {
                            const title = e.target.value;
                            setData((current) => ({
                                ...current,
                                title,
                                slug: current.slug ? current.slug : slugify(title),
                            }));
                        }}
                        className="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm text-neutral-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100"
                        placeholder="How to automate customer support"
                        required
                    />
                    {pageErrors.title && <p className="text-xs text-red-500">{pageErrors.title}</p>}
                </div>

                <div className="space-y-1">
                    <label className="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Slug</label>
                    <input
                        type="text"
                        value={data.slug}
                        onChange={(e) => updateField('slug', slugify(e.target.value))}
                        className="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm font-mono text-neutral-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100"
                        placeholder="how-to-automate-customer-support"
                        required
                    />
                    {pageErrors.slug && <p className="text-xs text-red-500">{pageErrors.slug}</p>}
                </div>

                <div className="space-y-1">
                    <label className="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Category</label>
                    <input
                        type="text"
                        value={data.category}
                        onChange={(e) => updateField('category', e.target.value)}
                        className="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm text-neutral-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100"
                        placeholder="Product updates, Tips, News"
                    />
                    {pageErrors.category && <p className="text-xs text-red-500">{pageErrors.category}</p>}
                </div>

                <div className="space-y-1">
                    <label className="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Publish</label>
                    <label className="flex items-center gap-2 rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm text-neutral-700 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-200">
                        <input
                            type="checkbox"
                            checked={data.published}
                            onChange={(e) => updateField('published', e.target.checked)}
                            className="rounded border-neutral-300 text-brand-600 focus:ring-brand-500"
                        />
                        Yes, publish this post
                    </label>
                </div>
            </div>

            <div className="space-y-1">
                <label className="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Blog image</label>
                <div className="flex items-start gap-4">
                    <div className="relative h-36 w-56 overflow-hidden rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-900">
                        {imageSrc ? (
                            <>
                                <img src={imageSrc} alt={data.title || 'Blog image'} className="h-full w-full object-cover" />
                                {imagePreview && (
                                    <button
                                        type="button"
                                        onClick={clearImage}
                                        className="absolute right-2 top-2 rounded-full bg-black/60 p-1 text-white hover:bg-black/75"
                                    >
                                        <X className="h-3.5 w-3.5" />
                                    </button>
                                )}
                            </>
                        ) : (
                            <div className="flex h-full w-full items-center justify-center text-neutral-300 dark:text-neutral-600">
                                <ImageIcon className="h-10 w-10" />
                            </div>
                        )}
                    </div>
                    <div className="space-y-2">
                        <input
                            ref={fileRef}
                            type="file"
                            accept="image/png,image/jpeg,image/jpg,image/gif,image/webp,image/svg+xml"
                            onChange={(e) => onPickImage(e.target.files?.[0] ?? null)}
                            className="block w-full text-sm text-neutral-600 file:mr-4 file:rounded-lg file:border-0 file:bg-brand-600 file:px-4 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-brand-700 dark:text-neutral-300"
                        />
                        <p className="text-xs text-neutral-500 dark:text-neutral-400">Upload a cover image for blog cards and post page.</p>
                        {pageErrors.image && <p className="text-xs text-red-500">{pageErrors.image}</p>}
                    </div>
                </div>
            </div>

            <div className="space-y-1">
                <label className="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Meta title</label>
                <input
                    type="text"
                    value={data.meta_title}
                    onChange={(e) => updateField('meta_title', e.target.value)}
                    className="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm text-neutral-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100"
                    placeholder="SEO title"
                />
                {pageErrors.meta_title && <p className="text-xs text-red-500">{pageErrors.meta_title}</p>}
            </div>

            <div className="space-y-1">
                <label className="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Meta description</label>
                <textarea
                    value={data.meta_description}
                    onChange={(e) => updateField('meta_description', e.target.value)}
                    rows={3}
                    className="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm text-neutral-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100"
                    placeholder="SEO description"
                />
                {pageErrors.meta_description && <p className="text-xs text-red-500">{pageErrors.meta_description}</p>}
            </div>

            <div className="space-y-1">
                <label className="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Blog content</label>
                <RichTextEditor
                    value={data.content}
                    onChange={(content) => updateField('content', content)}
                    placeholder="Write your blog post content here..."
                />
                {pageErrors.content && <p className="text-xs text-red-500">{pageErrors.content}</p>}
            </div>

            <div className="flex justify-end gap-2">
                <button type="button" onClick={onClose} className="rounded-lg border border-neutral-300 px-4 py-2 text-sm text-neutral-700 hover:bg-neutral-50 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800">
                    Cancel
                </button>
                <button type="submit" disabled={processing} className="rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700 disabled:opacity-50">
                    {post ? 'Update post' : 'Create post'}
                </button>
            </div>
        </form>
    );
}

export default function BlogPostsIndex({ posts }) {
    const [showCreate, setShowCreate] = useState(false);
    const [editing, setEditing] = useState(null);
    const sortedPosts = useMemo(() => posts ?? [], [posts]);

    return (
        <AdminLayout title="Blog Posts">
            <div className="max-w-6xl space-y-6">
                <div className="flex items-center justify-between gap-4">
                    <div className="flex items-center gap-3">
                        <BookOpenText className="h-6 w-6 text-brand-600 dark:text-brand-400" />
                        <div>
                            <h1 className="text-xl font-bold text-neutral-900 dark:text-white">Blog Posts</h1>
                            <p className="text-sm text-neutral-500 dark:text-neutral-400">Create and manage blog content for the website.</p>
                        </div>
                    </div>
                    <button
                        type="button"
                        onClick={() => setShowCreate(true)}
                        className="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700"
                    >
                        <Plus className="h-4 w-4" />
                        New post
                    </button>
                </div>

                {showCreate && (
                    <div className="rounded-2xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                        <h2 className="mb-4 text-base font-semibold text-neutral-900 dark:text-white">Create blog post</h2>
                        <PostForm onClose={() => setShowCreate(false)} />
                    </div>
                )}

                <div className="overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-neutral-50 dark:bg-neutral-800">
                                <tr>
                                    <th className="px-4 py-3 text-left font-medium text-neutral-600 dark:text-neutral-300">Title</th>
                                    <th className="px-4 py-3 text-left font-medium text-neutral-600 dark:text-neutral-300">Category</th>
                                    <th className="px-4 py-3 text-left font-medium text-neutral-600 dark:text-neutral-300">Meta title</th>
                                    <th className="px-4 py-3 text-left font-medium text-neutral-600 dark:text-neutral-300">Publish</th>
                                    <th className="px-4 py-3 text-right font-medium text-neutral-600 dark:text-neutral-300">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-neutral-100 dark:divide-neutral-800">
                                {sortedPosts.map((post) => (
                                    <tr key={post.id}>
                                        <td className="px-4 py-3">
                                            <div className="font-medium text-neutral-900 dark:text-white">{post.title}</div>
                                            <div className="font-mono text-xs text-neutral-400">/{post.slug}</div>
                                        </td>
                                        <td className="px-4 py-3 text-neutral-600 dark:text-neutral-300">{post.category || '—'}</td>
                                        <td className="px-4 py-3 text-neutral-600 dark:text-neutral-300">{post.meta_title || post.title}</td>
                                        <td className="px-4 py-3">
                                            <span className={`rounded-full px-2 py-0.5 text-xs font-medium ${post.published ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-neutral-100 text-neutral-500 dark:bg-neutral-800 dark:text-neutral-400'}`}>
                                                {post.published ? 'Yes' : 'No'}
                                            </span>
                                        </td>
                                        <td className="px-4 py-3">
                                            <div className="flex items-center justify-end gap-2">
                                                <button type="button" onClick={() => setEditing(post)} className="rounded-md p-1.5 text-neutral-400 hover:bg-neutral-100 hover:text-brand-600 dark:hover:bg-neutral-800">
                                                    <Pencil className="h-4 w-4" />
                                                </button>
                                                <button
                                                    type="button"
                                                    onClick={() => {
                                                        if (confirm('Delete this blog post?')) {
                                                            router.delete(route('admin.blog-posts.destroy', post.id));
                                                        }
                                                    }}
                                                    className="rounded-md p-1.5 text-neutral-400 hover:bg-neutral-100 hover:text-red-600 dark:hover:bg-neutral-800"
                                                >
                                                    <Trash2 className="h-4 w-4" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}

                                {!sortedPosts.length && (
                                    <tr>
                                        <td colSpan={5} className="px-4 py-10 text-center text-neutral-400 dark:text-neutral-500">
                                            No blog posts yet.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>

                {editing && (
                    <div className="rounded-2xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                        <h2 className="mb-4 text-base font-semibold text-neutral-900 dark:text-white">Edit blog post</h2>
                        <PostForm post={editing} onClose={() => setEditing(null)} />
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
