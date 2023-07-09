<?php

namespace App\Console\Commands;

use App\Models\OrigamiProducts;
use App\Services\DropService;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

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

    /**
     * Create a new command instance.
     *
     */
    public function __construct(DropService $dropService)
    {
        $this->dropService = $dropService;
        parent::__construct();
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(): void
    {
        $yourApiKey = config('app.open_ai');
        //$client = new OpenAi($yourApiKey);



//        $prompts = OrigamiProducts::query()
//            ->where('provider', 'royal')
//            ->where('promt', '');

        $prompts = OrigamiProducts::query()
            ->where('provider', 'royal')
            ->where('vendorCode', 'KHO5078')->first();
        dd($prompts->promt);

        if($prompts) {
            $url = 'https://api.openai.com/v1/chat/completions';

            $client = new Client();

            $data = [
                "messages" => [
                    ["role" => "system", "content" => "You are a helpful assistant."],
                    [
                        "role" => "user",
                        "content" => $prompts->promt. " Каждый абзац обрамить в тег <p> добавить тег <ul><li> если нужно ."
                    ],
                ],
                'model' => 'gpt-3.5-turbo-16k',
                'temperature' => 0.7,
                'max_tokens' => 1000,
                'frequency_penalty' => 0,
                'presence_penalty' => 0.6,
            ];
            $response = $client->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $yourApiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => $data,
            ]);

            $result = json_decode($response->getBody(), true);

            $assistantResponse = $result['choices'][0]['message']['content'];

            dd($assistantResponse);
        }
    }

    private function translateAi()
    {
    }

}
