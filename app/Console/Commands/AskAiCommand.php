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


    function translateText($text, $sourceLang, $targetLang) {
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

    public function getKeywords(string $prompt){
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
            'temperature' => 0.7,
            'max_tokens' => 300,
            'frequency_penalty' => 0,
            'presence_penalty' => 0.6,
        ];

        $response= $client->post($url, [
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
//            ->where('description', '=', '')
//            ->where('description_ua', '=', '')
            ->get();

        if ($prompts) {
            foreach ($prompts as $prompt) {
                $translateRu = 'Ключевые слова на русском языке для "' . $prompt->name.'" с разделителем "," (без кода товара, ширины и высоты)';
                $translateUa = 'Ключові слова для "' . $prompt->name.'" с разделителем "," (без кода товара, ширины и высоты)';
//                $copyright = $prompt->promt . ". Каждый абзац твоего текста обрамить в тег <p> добавить тег <ul><li> если нужно .";

                $resultRu = $this->getKeywords($translateRu)['choices'][0]['message']['content'];
                $resultUa = $this->getKeywords($translateUa)['choices'][0]['message']['content'];
                $this->info($prompt->id . ') ' . $this->removeQuotes($resultRu));
                $this->info($prompt->id . ') ' . $this->removeQuotes($resultUa));
                $prompt->name = $this->removeQuotes($resultRu);
                $prompt->nameUa = $this->removeQuotes($resultUa);
                $prompt->save();
                sleep(2);
            }
        }
    }
}
