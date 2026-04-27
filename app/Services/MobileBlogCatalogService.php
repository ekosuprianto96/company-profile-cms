<?php

namespace App\Services;

use App\Models\Blog;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MobileBlogCatalogService
{
    public function all(): Collection
    {
        return Blog::query()
            ->with(['kategori'])
            ->where('an', 1)
            ->latest()
            ->get()
            ->map(fn (Blog $blog) => $this->mapBlog($blog))
            ->values();
    }

    public function featured(): Collection
    {
        return Blog::query()
            ->with(['kategori'])
            ->where('an', 1)
            ->latest()
            ->take(3)
            ->get()
            ->map(fn (Blog $blog) => $this->mapBlog($blog))
            ->values();
    }

    public function findBySlug(string $slug): ?array
    {
        $blog = Blog::query()
            ->with(['kategori'])
            ->where('slug', $slug)
            ->where('an', 1)
            ->first();

        if (! $blog) {
            return null;
        }

        return $this->mapBlog($blog, true);
    }

    public function categories(): array
    {
        return Blog::query()
            ->with(['kategori'])
            ->where('an', 1)
            ->get()
            ->map(fn (Blog $blog) => $blog->kategori?->name)
            ->filter()
            ->unique()
            ->values()
            ->map(fn (string $category) => [
                'label' => $category,
                'slug' => Str::slug($category),
            ])
            ->all();
    }

    private function mapBlog(Blog $blog, bool $withContent = false): array
    {
        $contentText = $this->compactWhitespace($this->normalizeHtmlToText($blog->content));
        $excerpt = trim((string) Str::limit($this->compactWhitespace($blog->description ?: $contentText), 120));
        $readingTime = max(1, (int) ceil(str_word_count($contentText) / 180));

        return [
            'id' => $blog->id,
            'title' => $blog->title,
            'slug' => $blog->slug,
            'category' => $blog->kategori?->name ?? 'Blog',
            'category_slug' => Str::slug((string) ($blog->kategori?->name ?? 'blog')),
            'excerpt' => $excerpt,
            'content' => $withContent ? $contentText : null,
            'cover_image_url' => empty($blog->thumbnail) ? null : image_url('blogs', $blog->thumbnail),
            'views' => (int) ($blog->views ?? 0),
            'reading_time' => $readingTime,
            'reading_time_label' => $readingTime . ' menit baca',
            'created_at' => optional($blog->created_at)?->toISOString(),
            'updated_at' => optional($blog->updated_at)?->toISOString(),
        ];
    }

    private function normalizeHtmlToText(?string $html): string
    {
        $content = (string) $html;
        $content = preg_replace('/<\s*br\s*\/?\s*>/i', "\n", $content) ?? $content;
        $content = preg_replace('/<\/p>\s*<p[^>]*>/i', "\n\n", $content) ?? $content;
        $content = strip_tags($content);
        $content = preg_replace("/\n{3,}/", "\n\n", $content) ?? $content;

        return trim(html_entity_decode($content, ENT_QUOTES | ENT_HTML5));
    }

    private function compactWhitespace(?string $text): string
    {
        return trim(preg_replace('/\s+/u', ' ', (string) $text) ?? '');
    }
}
