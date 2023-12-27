<?php

namespace App\Http\Controllers\Apis\v1;

use App\Http\Controllers\Controller;
use App\Contracts\ArticleServiceInterface;

class ArticleController extends Controller
{
    public function __construct(protected ArticleServiceInterface $articleService)
    {
    }

    public function getArticles()
    {
        $articles = $this->articleService->getArticles();
        return responeSuccess($articles, "Articles retrieved successfully");
    }

    public function showArticle($slug)
    {
        $article = $this->articleService->showArticle($slug);
        return responeSuccess($article, "Article retrieved successfully");
    }

    public function getCategories()
    {
        $categories = $this->articleService->getCategories();
        return responeSuccess($categories, "Categories retrieved successfully");
    }

    public function getSources()
    {
        $sources = $this->articleService->getSources();
        return responeSuccess($sources, "Sources retrieved successfully");
    }

    public function getAuthors()
    {
        $authors = $this->articleService->getAuthors();
        return responeSuccess($authors, "Authors retrieved successfully");
    }
}
