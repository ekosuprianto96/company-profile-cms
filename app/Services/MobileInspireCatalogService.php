<?php

namespace App\Services;

use App\Models\InspirePost;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MobileInspireCatalogService
{
    public function __construct(
        protected InspirePostService $inspirePostService
    ) {}

    public function all(): Collection
    {
        return $this->inspirePostService->active()
            ->map(fn (InspirePost $post) => $this->mapPost($post))
            ->values();
    }

    public function featured(): Collection
    {
        return $this->inspirePostService->active()
            ->filter(fn (InspirePost $post) => $post->is_featured)
            ->take(4)
            ->map(fn (InspirePost $post) => $this->mapPost($post))
            ->values();
    }

    public function findBySlug(string $slug): ?array
    {
        $post = $this->inspirePostService->findBySlug($slug);

        if (! $post || ! $post->is_published) {
            return null;
        }

        return $this->mapPost($post, true);
    }

    public function categories(): array
    {
        return $this->inspirePostService->active()
            ->pluck('category')
            ->filter()
            ->unique()
            ->values()
            ->map(fn ($category) => [
                'label' => (string) $category,
                'slug' => Str::slug((string) $category),
            ])
            ->all();
    }

    private function mapPost(InspirePost $post, bool $withContent = false): array
    {
        $contentText = $this->inspirePostService->normalizeHtmlToText($post->content);
        $summary = trim((string) ($post->summary ?: \Illuminate\Support\Str::limit($contentText, 120)));

        return [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'category' => $post->category,
            'summary' => $summary,
            'content' => $withContent ? $contentText : null,
            'cover_image_url' => $post->cover_image_url,
            'accent_color' => $post->accent_color,
            'reading_time' => (int) $post->reading_time,
            'reading_time_label' => ((int) $post->reading_time) . ' menit baca',
            'sort_order' => (int) $post->sort_order,
            'is_featured' => (bool) $post->is_featured,
            'is_published' => (bool) $post->is_published,
            'created_at' => optional($post->created_at)?->toISOString(),
        ];
    }
}
