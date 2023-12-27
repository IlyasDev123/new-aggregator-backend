<?php

namespace App\Console\Commands;

use App\Models\Author;

use App\Models\Source;
use GuzzleHttp\Client;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Console\Command;

class ScrapNewYorkTimeArticle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:scrap-new-york-time-article';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will scrap news data from New York Time API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $categories  = Category::get();
            foreach ($categories as $category) {

                $categoryName = Str::lower($category->name);

                $client = new Client();
                $url = "https://api.nytimes.com/svc/search/v2/articlesearch.json?q={$categoryName}&api-key=" . env('NEWYORK_TIMES_API_KEY');
                $response = $client->get($url);
                $data = $response->getBody()->getContents();
                $data = json_decode($data, true);
                if (!isset($data['response']['docs'])) continue;
                foreach ($data['response']['docs'] as $article) {
                    $source =  Source::firstOrCreate(
                        ['name' => Str::upper($article['source'])],
                        ['name' => Str::upper($article['source'])]
                    );

                    $author =  Author::firstOrCreate(
                        ['name' => Str::upper($article['byline']['original'])],
                        ['name' => Str::upper($article['byline']['original'])]
                    );

                    $source->articles()->firstOrCreate(
                        ['slug' => Str::slug($article['headline']['main'])],
                        [
                            'title' => $article['headline']['main'],
                            'slug' => Str::slug($article['headline']['main']),
                            'description' => $article['abstract'],
                            'category_id' => $category->id,
                            'author_id' => $author->id,
                            'url' => $article['web_url'],
                            'url_to_image' => isset($article['multimedia']['url']) ? env('NEWYORK_TIME_URL') . isset($article['multimedia']['url'])  : "https://via.placeholder.com/150",
                            'content' => $this->serializeContent($article) ?? null,
                            'published_at' => \Carbon\Carbon::parse($article['pub_date'])->format('Y-m-d H:i:s'),
                        ]
                    );
                }
            }

            // DB::commit();
            createDebugLogFile('NewsAPI/fetch-news-data', 'News data fetched successfully');
            return true;
        } catch (\Throwable $th) {
            // DB::rollBack();
            createDebugLogFile('NewYorkTime/error', $th->getMessage());
            return false;
        }
    }

    public function serializeContent($content)
    {
        return  $content = "
        <div class='article-content'>
            <h5 class='article-content__text'>
                {$content['lead_paragraph']}
            </h5>
            <div>
            <p class='snipit-class'>
                {$content['snippet']}
            </p>
            </div>
        </div>";
    }
}
