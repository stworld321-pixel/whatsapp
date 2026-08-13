<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Services\StorageManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class BlogPostController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/BlogPosts/Index', [
            'posts' => BlogPost::latest()->get()->map(fn (BlogPost $post) => $this->serializePost($post)),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'slug' => ['required', 'string', 'max:150', 'alpha_dash', 'unique:blog_posts,slug'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,gif,webp,svg', 'max:4096'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'category' => ['nullable', 'string', 'max:100'],
            'published' => ['boolean'],
        ]);

        unset($validated['image']);

        $post = new BlogPost($validated);
        $this->storeImage($request, $post);
        $post->save();

        return redirect()->route('admin.blog-posts.index')->with('success', __('Blog post created.'));
    }

    public function update(Request $request, BlogPost $blogPost): RedirectResponse
    {
        $validated = $request->validate([
            'slug' => ['required', 'string', 'max:150', 'alpha_dash', "unique:blog_posts,slug,{$blogPost->id}"],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,gif,webp,svg', 'max:4096'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'category' => ['nullable', 'string', 'max:100'],
            'published' => ['boolean'],
        ]);

        unset($validated['image']);

        $blogPost->fill($validated);
        $this->storeImage($request, $blogPost);
        $blogPost->save();

        return redirect()->route('admin.blog-posts.index')->with('success', __('Blog post updated.'));
    }

    public function destroy(BlogPost $blogPost): RedirectResponse
    {
        if ($blogPost->image_path) {
            $this->deleteImage($blogPost);
        }
        $blogPost->delete();

        return redirect()->route('admin.blog-posts.index')->with('success', __('Blog post deleted.'));
    }

    private function serializePost(BlogPost $post): array
    {
        return [
            'id' => $post->id,
            'slug' => $post->slug,
            'title' => $post->title,
            'content' => $post->content,
            'image_url' => $this->imageUrl($post),
            'meta_title' => $post->meta_title,
            'meta_description' => $post->meta_description,
            'category' => $post->category,
            'published' => $post->published,
            'created_at' => $post->created_at?->toDateString(),
        ];
    }

    private function storeImage(Request $request, BlogPost $post): void
    {
        if (! $request->hasFile('image')) {
            return;
        }

        if ($post->exists && $post->image_path) {
            $this->deleteImage($post);
        }

        $sm = app(StorageManager::class);
        $disk = $sm->diskName();
        $file = $request->file('image');
        $path = $sm->prefixedPath('blog/'.Str::uuid().'.'.$file->getClientOriginalExtension());
        $sm->disk()->putFileAs(dirname($path), $file, basename($path));

        $post->image_path = $path;
        $post->image_disk = $disk;
    }

    private function deleteImage(BlogPost $post): void
    {
        $disk = $post->image_disk ?: 'public';
        app(StorageManager::class)->ensureDiskReady($disk);
        Storage::disk($disk)->delete($post->image_path);
    }

    private function imageUrl(BlogPost $post): ?string
    {
        if (! $post->image_path) {
            return null;
        }

        $disk = $post->image_disk ?: 'public';
        app(StorageManager::class)->ensureDiskReady($disk);

        return route('blog.image', [
            'slug' => $post->slug,
            'v' => $post->updated_at?->timestamp ?? time(),
        ]);
    }
}
