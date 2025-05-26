<?php

namespace App\Services;

use Illuminate\Support\Str;
use App\Repositories\BlogRepository;
use App\Http\Requests\BlogStoreRequest;
use App\Http\Requests\BlogUpdteRequest;

class BlogService
{
    public function __construct(
        protected BlogRepository $blog
    ) {}

    public function all()
    {
        return $this->blog->all();
    }

    public function findBlog($id)
    {
        return $this->blog->find($id);
    }

    public function findBySlug(string $slug)
    {
        return $this->blog->where('slug', $slug)->first();
    }

    public function create(BlogStoreRequest $request)
    {

        // check exists duplicate slug
        $this->existsSlug(Str::slug($request->title));

        if ($request->hasFile('thumbnail')) {
            $destination = public_path('assets/images/blogs');
            $file = $request->file('thumbnail');
            $ext = $file->getClientOriginalExtension();
            $newName = now()->format('ymd') . '-' . Str::uuid() . '.' . $ext;

            if (!is_dir($destination)) {
                mkdir($destination, 077, true);
            }

            $file->move($destination, $newName);
        }

        return $this->blog->create([
            'slug' => Str::slug($request->title),
            'thumbnail' => $newName ?? null,
            'title' => Str::title($request->title),
            'keywords' => join(',', $request->keywords ?? []),
            ...$request->only('content', 'an', 'kategori_id')
        ]);
    }

    public function existsSlug(string $slug, string $not = ''): void
    {
        $blog = $this->blog->where('slug', $slug);

        if (!empty($not)) {
            $blog = $blog->where('slug', '<>', $not);
        }

        if ($blog->exists()) {
            throw new \Exception('Terdapat duplikasi slug, pastikan title unique dan belum pernah ditambahkan sebelumnya', 400);
        }
    }

    public function update(BlogUpdteRequest $request, $slug)
    {
        // check exists duplicate slug
        $blog = $this->findBySlug($slug);
        $this->existsSlug(Str::slug($request->title), $blog->slug ?? null);

        if ($request->hasFile('thumbnail')) {

            $this->removeIfExistsImage($blog);

            $destination = public_path('assets/images/blogs');
            $file = $request->file('thumbnail');
            $ext = $file->getClientOriginalExtension();
            $newName = now()->format('ymd') . '-' . Str::uuid() . '.' . $ext;

            if (!is_dir($destination)) {
                mkdir($destination, 077, true);
            }

            $file->move($destination, $newName);
        }

        return $this->blog->update($slug, [
            'slug' => Str::slug($request->title),
            'thumbnail' => $newName ?? $blog->thumbnail,
            'title' => Str::title($request->title),
            'keywords' => join(',', $request->keywords ?? []),
            ...$request->only('content', 'an', 'kategori_id')
        ]);
    }

    public function removeIfExistsImage($blog)
    {
        if (isset($blog->thumbnail)) {
            $fileImage = public_path('assets/images/blogs/' . $blog->thumbnail);
            if (file_exists($fileImage)) {
                unlink($fileImage);
            }
        }
    }

    public function delete($slug)
    {
        $blog = $this->findBySlug($slug);
        $this->removeIfExistsImage($blog);
        return $this->blog->delete($slug);
    }
}
