<?php

namespace App\Services;

use App\Models\Author;
use App\Models\Source;
use App\Models\Article;

use App\Models\Category;;

use App\Contracts\ArticleServiceInterface;

class ArticleService implements ArticleServiceInterface
{
    public function getArticles()
    {
        $searchQuery = request()->query('q');
        $categoryId = request()->query('category_id');
        $sourceId = request()->query('source_id');
        $startDate = request()->query('start_date');
        $endDate = request()->query('end_date');

        return Article::with('category', 'source', 'author')
            ->categoryPreferences()
            ->sourcePreferences()
            ->authorPreferences()
            ->when($searchQuery, fn ($query, $search) => $query->search($search))
            ->when($categoryId, fn ($query, $category) => $query->category($category))
            ->when($sourceId, fn ($query, $source) => $query->source($source))
            ->when($startDate && !$endDate, fn ($query) => $query->where('published_at', $startDate))
            ->when($startDate && $endDate, fn ($query) => $query->betweenDates($startDate, $endDate))
            ->orderByDesc('published_at')
            ->paginate(prePageLimit());
    }


    public function showArticle($slug)
    {
        return Article::where('slug', $slug)->with('category', 'source', 'author')->first();
    }

    public function getCategories()
    {
        return Category::get();
    }

    public function getSources()
    {
        return Source::get();
    }

    public function getAuthors()
    {
        return Author::get();
    }
}
