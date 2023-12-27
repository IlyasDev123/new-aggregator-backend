<?php

namespace App\Models;

use App\Models\Author;
use App\Models\Source;
use App\Models\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'author_id',
        'category_id',
        'source_id',
        'url',
        'url_to_image',
        'content',
        'published_at'
    ];

    protected $casts = [
        'published_at' => 'datetime:M d, Y h A',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function source()
    {
        return $this->belongsTo(Source::class);
    }

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    public function scopeCategory($query, $category)
    {
        return $query->where('category_id', $category);
    }

    public function scopeSource($query, $source)
    {
        return $query->where('source_id', $source);
    }

    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('published_at', [$startDate, $endDate]);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('title', 'like', "%{$search}%")
            ->orWhere('description', 'like', "%{$search}%")
            ->orWhere('content', 'like', "%{$search}%");
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCategoryPreferences($query)
    {
        $categoryIds = auth()->user()->categories->pluck('id');
        if ($categoryIds->count() > 0) {
            return $query->whereIn('category_id', $categoryIds);
        }
        return $query->whereNotNull('category_id');
    }

    public function scopeSourcePreferences($query)
    {
        $sourceIds = auth()->user()->sources->pluck('id');
        if ($sourceIds->count() > 0) {
            return $query->whereIn('source_id', $sourceIds);
        }
        return $query->whereNotNull('source_id');
    }

    public function scopeAuthorPreferences($query)
    {
        $authorsIds = auth()->user()->authors->pluck('id');
        if ($authorsIds->count() > 0) {
            return $query->whereIn('author_id', $authorsIds);
        }
        return $query->whereNotNull('author_id');
    }
}
