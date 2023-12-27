<?php

namespace App\Console\Commands;

use App\Models\Author;
use App\Models\Source;
use GuzzleHttp\Client;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Console\Command;

class ScrapGuardianArticle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:scrap-guardian-article';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will scrap news data from Guardian API';

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
                $url = "https://content.guardianapis.com/search?q={$categoryName}&show-fields=all&api-key=" . env('GUARDIAN_API_KEY');
                $response = $client->get($url);
                $data = $response->getBody()->getContents();
                $data = json_decode($data, true);
                if (!isset($data['response']['results'])) continue;
                foreach ($data['response']['results'] as $article) {
                    $source =  Source::firstOrCreate(
                        ['name' => Str::upper($article['fields']['publication'])],
                        ['name' => Str::upper($article['fields']['publication'])]
                    );
                    if (!isset($article['fields']['byline'])) {
                        $author =  Author::firstOrCreate(
                            ['name' => "Unknown"],
                            ['name' => "Unknown"]
                        );
                    } else {
                        $author =  Author::firstOrCreate(
                            ['name' => Str::upper($article['fields']['byline'])],
                            ['name' => Str::upper($article['fields']['byline'])]
                        );
                    }

                    $source->articles()->firstOrCreate(
                        ['slug' => Str::slug($article['webTitle'])],
                        [
                            'title' => $article['webTitle'],
                            'slug' => Str::slug($article['webTitle']),
                            'description' => Str::words($article['fields']['bodyText'], 200, '...'),
                            'category_id' => $category->id,
                            'author_id' => $author->id,
                            'url' => $article['webUrl'],
                            'url_to_image' => $article['fields']['thumbnail'],
                            'content' => $article['fields']['body'] ?? null,
                            'published_at' => \Carbon\Carbon::parse($article['fields']['firstPublicationDate'])->format('Y-m-d H:i:s'),
                        ]
                    );
                    sleep(1);
                }
            }

            // DB::commit();
            createDebugLogFile('Guardian/success', 'News data fetched successfully');
            return true;
        } catch (\Throwable $th) {
            // DB::rollBack();
            createDebugLogFile('Guardian/error', $th->getMessage());
            return false;
        }
    }
}
