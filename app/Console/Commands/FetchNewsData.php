<?php

namespace App\Console\Commands;

use App\Models\Author;
use App\Models\Source;
use GuzzleHttp\Client;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FetchNewsData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-news-api-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will fetch news data from News API';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // DB::beginTransaction();
        try {
            $categories  = Category::get();
            foreach ($categories as $category) {

                $categoryName = Str::lower($category->name);
                $client = new Client();
                $url = "https://newsapi.org/v2/everything?q={$categoryName}&apiKey=" . env('NEWS_API_KEY');
                $response = $client->get($url);
                $data = $response->getBody()->getContents();
                $data = json_decode($data, true);
                if (!isset($data['articles'])) continue;
                foreach ($data['articles'] as $article) {
                    $source =  Source::firstOrCreate(
                        ['name' => Str::upper($article['source']['name'])],
                        ['name' => Str::upper($article['source']['name'])]
                    );
                    $author =  Author::firstOrCreate(
                        ['name' => Str::upper($article['author'])],
                        ['name' => Str::upper($article['author'])]
                    );
                    $source->articles()->firstOrCreate(
                        ['slug' => Str::slug($article['title'])],
                        [
                            'title' => $article['title'],
                            'slug' => Str::slug($article['title']),
                            'description' => $article['description'],
                            'author_id' => $author->id,
                            'category_id' => $category->id,
                            'url' => $article['url'],
                            'url_to_image' => $article['urlToImage'] ?? "https://via.placeholder.com/150",
                            'content' => $article['content'] ?? null,
                            'published_at' => \Carbon\Carbon::parse($article['publishedAt'])->format('Y-m-d H:i:s'),
                        ]
                    );
                }
            }

            // DB::commit();
            createDebugLogFile('NewsAPI/fetch-news-data', 'News data fetched successfully');
            return true;
        } catch (\Throwable $th) {
            // DB::rollBack();
            createDebugLogFile('NewsAPI/error', $th->getMessage());
            return false;
        }
    }
}
