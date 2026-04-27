<?php

namespace App\Models;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Model;

class InspirePost extends Model
{
    use Blameable;

    protected $table = 'inspire_posts';

    protected $guarded = ['id'];

    protected $appends = [
        'cover_image_url',
    ];

    protected function casts(): array
    {
        return [
            'reading_time' => 'integer',
            'sort_order' => 'integer',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        if (empty($this->thumbnail)) {
            return null;
        }

        return image_url('inspire-posts', $this->thumbnail);
    }
}
