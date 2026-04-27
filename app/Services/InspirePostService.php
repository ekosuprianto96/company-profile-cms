<?php

namespace App\Services;

use App\Http\Requests\InspirePostStoreRequest;
use App\Http\Requests\InspirePostUpdateRequest;
use App\Models\InspirePost;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class InspirePostService
{
    public function all()
    {
        return InspirePost::query()
            ->with(['createdBy.account', 'updatedBy.account'])
            ->latest()
            ->get();
    }

    public function active()
    {
        return InspirePost::query()
            ->where('is_published', true)
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->get();
    }

    public function findBySlug(string $slug): ?InspirePost
    {
        return InspirePost::query()->where('slug', $slug)->first();
    }

    public function create(InspirePostStoreRequest $request): InspirePost
    {
        $slug = Str::slug($request->title);
        $this->ensureSlugIsUnique($slug);

        $thumbnail = $this->storeThumbnail($request->file('thumbnail'));

        return InspirePost::query()->create([
            'title' => Str::title($request->title),
            'slug' => $slug,
            'category' => Str::title($request->category),
            'summary' => $request->summary,
            'content' => $request->content,
            'thumbnail' => $thumbnail,
            'accent_color' => $request->accent_color,
            'reading_time' => (int) $request->reading_time,
            'sort_order' => (int) ($request->sort_order ?? 0),
            'is_featured' => $request->boolean('is_featured'),
            'is_published' => $request->boolean('is_published', true),
        ]);
    }

    public function update(InspirePostUpdateRequest $request, string $slug): InspirePost
    {
        $post = $this->findBySlug($slug);

        if (! $post) {
            throw new \RuntimeException('Data inspire tidak ditemukan.', 404);
        }

        $newSlug = Str::slug($request->title);
        $this->ensureSlugIsUnique($newSlug, $post->slug);

        $thumbnail = $this->storeThumbnail($request->file('thumbnail'));
        if ($thumbnail) {
            $this->removeThumbnailIfExists($post);
        }

        $post->update([
            'title' => Str::title($request->title),
            'slug' => $newSlug,
            'category' => Str::title($request->category),
            'summary' => $request->summary,
            'content' => $request->content,
            'thumbnail' => $thumbnail ?? $post->thumbnail,
            'accent_color' => $request->accent_color,
            'reading_time' => (int) $request->reading_time,
            'sort_order' => (int) ($request->sort_order ?? 0),
            'is_featured' => $request->boolean('is_featured'),
            'is_published' => $request->boolean('is_published', true),
        ]);

        return $post->refresh();
    }

    public function delete(string $slug): void
    {
        $post = $this->findBySlug($slug);

        if (! $post) {
            throw new \RuntimeException('Data inspire tidak ditemukan.', 404);
        }

        $this->removeThumbnailIfExists($post);
        $post->delete();
    }

    public function ensureSlugIsUnique(string $slug, string $exceptSlug = ''): void
    {
        $query = InspirePost::query()->where('slug', $slug);

        if ($exceptSlug !== '') {
            $query->where('slug', '<>', $exceptSlug);
        }

        if ($query->exists()) {
            throw new \RuntimeException('Slug sudah digunakan, silakan ubah judulnya.', 400);
        }
    }

    private function storeThumbnail(?UploadedFile $file): ?string
    {
        if (! $file) {
            return null;
        }

        $destination = public_path('assets/images/inspire-posts');
        if (! is_dir($destination)) {
            mkdir($destination, 0777, true);
        }

        $extension = $file->getClientOriginalExtension() ?: 'jpg';
        $fileName = now()->format('ymdHis') . '-' . Str::uuid() . '.' . $extension;
        $file->move($destination, $fileName);

        return $fileName;
    }

    private function removeThumbnailIfExists(InspirePost $post): void
    {
        if (empty($post->thumbnail)) {
            return;
        }

        $path = public_path('assets/images/inspire-posts/' . $post->thumbnail);
        if (file_exists($path)) {
            @unlink($path);
        }
    }

    public function normalizeHtmlToText(?string $html): string
    {
        $content = (string) $html;
        $content = preg_replace('/<\s*br\s*\/?\s*>/i', "\n", $content) ?? $content;
        $content = preg_replace('/<\/p>\s*<p[^>]*>/i', "\n\n", $content) ?? $content;
        $content = strip_tags($content);
        $content = preg_replace("/\n{3,}/", "\n\n", $content) ?? $content;

        return trim(html_entity_decode($content, ENT_QUOTES | ENT_HTML5));
    }
}
