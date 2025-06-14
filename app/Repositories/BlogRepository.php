<?php

namespace App\Repositories;

use App\Models\Blog;

class BlogRepository extends BaseRepositori
{
    protected $fillable = [];

    public function __construct()
    {
        $this->setModel(Blog::class);
        parent::__construct();
    }

    public function create(array $param = [])
    {
        return $this->model->create($param);
    }

    public function update($slug, array $param = [])
    {
        $blog = $this->model->where('slug', $slug)->first();
        if (!$blog) throw new \Exception('Data tidak ditemukan', 404);
        return $blog->update($param);
    }

    public function delete($slug)
    {
        $blog = $this->model->where('slug', $slug)->first();
        if (!$blog) throw new \Exception('Data tidak ditemukan', 404);
        return $blog->delete();
    }

    public function incerementViews($slug)
    {
        $blog = $this->model->where('slug', $slug)->first();

        if ($blog) {
            return $blog->increment('views');
        }

        return false;
    }
}
