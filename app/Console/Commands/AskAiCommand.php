<?php

namespace App\Console\Commands;

use App\Models\OrigamiProducts;
use App\Services\DropService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use JetBrains\PhpStorm\NoReturn;

class AskAiCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-ask';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update description';

    private string $yourApiKey;

    /**
     * Create a new command instance.
     *
     */
    public function __construct(DropService $dropService)
    {
        $this->dropService = $dropService;
        parent::__construct();
        $this->yourApiKey = config('app.open_ai');
    }

    private function removeQuotes($text)
    {
        if ($text[0] === '"' && $text[strlen($text) - 1] === '"') {
            $text = substr($text, 1, -1);
        }
        return $text;
    }


    function translateText($text, $sourceLang, $targetLang)
    {
        $apiUrl = 'https://api.openai.com/v1/engines/davinci-codex/completions';

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->yourApiKey,
        ];

        $payload = [
            'prompt' => "Translate the following $sourceLang text to $targetLang: '$text'",
            'max_tokens' => 100,
            'temperature' => 0.7,
            'n' => 1,
            'stop' => null,
            'log_level' => 'info',
        ];

        $client = new Client();
        $response = $client->post($apiUrl, [
            'headers' => $headers,
            'json' => $payload,
        ]);

        $responseData = json_decode($response->getBody(), true);
        $translation = trim($responseData['choices'][0]['text']);

        return $translation;
    }

    public function getKeywords(string $prompt)
    {
        $url = 'https://api.openai.com/v1/chat/completions';
        $client = new Client();
        $data = [
            "messages" => [
                [
                    "role" => "system",
                    "content" => "You are a helpful assistant."
                ],
                [
                    "role" => "user",
                    "content" => $prompt
                ],
            ],
            'model' => 'gpt-3.5-turbo',
//                    'model' => 'text-moderation-latest',
            'temperature' => 0.8,
            'max_tokens' => 100,
            'frequency_penalty' => 0,
            'presence_penalty' => 0.6,
        ];

        $response = $client->post($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->yourApiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => $data,
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * @throws GuzzleException
     */
    #[NoReturn] public function handle(): void
    {
        $prompts = OrigamiProducts::query()
            ->where('provider', 'royal')
            ->where('name', '!=', '')
            ->where('id','>', 31422)
            ->get();

        if ($prompts) {
            foreach ($prompts as $prompt) {
                $translateRu = 'Написать ключевые SEO слова с разделителем "," для текста - "' . $prompt->name . '" и такими условиями - цифр не должно быть, без кода товара по типу CH114, без сантиметров, без ширины без высоты, без штук, без шт';
                //$copyright = $prompt->promt . " Каждый абзац твоего текста обрамить в тег <p> добавить тег <ul><li> если нужно";
                $resultRu = $this->getKeywords($translateRu)['choices'][0]['message']['content'];
                //$translateUa = 'Перекласти на українську мову текст - "' . $resultRu . '"';


                //$translateUa = 'Написати ключові SEO слова з роздільником "," для тексту - "' . $prompt->nameUa . '" і такими умовами - цифр не повинно бути, без коду товару на кшталт CH114, без сантиметрів, без ширини без висоти, без штук, без шт';

                //$resultUa = $this->getKeywords($translateUa)['choices'][0]['message']['content'];
                $this->info($prompt->id . ') ' . $this->removeQuotes($resultRu));
                //$this->info($prompt->id . ') ' . $this->removeQuotes($resultUa));
                $prompt->keywords = $this->removeQuotes($resultRu);
                //$prompt->keywordsUa = $this->removeQuotes($resultUa);
                $prompt->save();
                //sleep(2);
            }
        }
    }
}
