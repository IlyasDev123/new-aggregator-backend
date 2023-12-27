<?php

namespace App\Contracts;

interface ArticleServiceInterface
{
    public function getArticles();
    public function showArticle($slug);
    public function getCategories();
    public function getSources();
    public function getAuthors();
}
