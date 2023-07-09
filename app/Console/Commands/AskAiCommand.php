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

    /**
     * Create a new command instance.
     *
     */
    public function __construct(DropService $dropService)
    {
        $this->dropService = $dropService;
        parent::__construct();
    }

    private function removeQuotes($text)
    {
        if ($text[0] === '"' && $text[strlen($text) - 1] === '"') {
            $text = substr($text, 1, -1);
        }
        return $text;
    }

    private function getDivContent($url): string
    {
        $divContent = '';
        $url = 'https://royaltoys.com.ua/product/kartina-po-nomeram-venecianskoe-taksi-40-50sm-kho2749-/';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($curl);
        curl_close($curl);
        echo $response;
        dd(1);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($curl);
        echo $response;
        curl_close($curl);
        $dom = new \DOMDocument();
        $dom->loadHTML($response);
        $xpath = new \DOMXPath($dom);
        $divXPath = "//div[@itemprop='description']";
        $divElements = $xpath->query($divXPath);
        var_dump($divElements);
        if ($divElements->length > 0) {
            $divContent = $dom->saveHTML($divElements->item(0));
            echo $divContent;
        } else {
            echo 'Div not found.';
        }

        return $divContent;
    }

    /**
     * @throws GuzzleException
     */
    #[NoReturn] public function handle(): void
    {
        $yourApiKey = config('app.open_ai');

        $url = 'https://royaltoys.com.ua/product/kartina-po-nomeram-venecianskoe-taksi-40-50sm-kho2749-/';
        $text = $this->getDivContent($url);
        dd($text);
        //$client = new OpenAi($yourApiKey);


//        $prompts = OrigamiProducts::query()
//            ->where('provider', 'royal')
//            ->where('promt', '');


        $prompts = OrigamiProducts::query()
            ->where('provider', 'royal')
            ->where('name', '!=', '')
//            ->where('description', '=', '')
            ->where('nameUa', '==', '')
//            ->where('description_ua', '=', '')
            ->get();

//        foreach ($prompts as $prompt) {
//            $prompt->nameUa =  $this->removeQuotes($prompt->nameUa);
//            $prompt->save();
//        }
//        dd(1);
        dump($prompts);

        if ($prompts) {
            foreach ($prompts as $prompt) {
                $translate = 'сделать перевод текста на украинский язык - ' . $prompt->name;
                $copyright = $prompt->promt . ". Каждый абзац твоего текста обрамить в тег <p> добавить тег <ul><li> если нужно .";
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
                            "content" => $translate
                        ],
                    ],
                    'model' => 'gpt-3.5-turbo',
//                    'model' => 'text-moderation-latest',
                    'temperature' => 0.7,
                    'max_tokens' => 1000,
                    'frequency_penalty' => 0,
                    'presence_penalty' => 0.6,
                ];

                $handle = curl_init('https://enz5dikc9mvgr.x.pipedream.net/');
                $encodedData = json_encode($data);
                curl_setopt($handle, CURLOPT_POST, 1);
                curl_setopt($handle, CURLOPT_POSTFIELDS, $encodedData);
                curl_setopt($handle, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

                $response = $client->post($url, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $yourApiKey,
                        'Content-Type' => 'application/json',
                    ],
                    'json' => $data,
                ]);
                $result = json_decode($response->getBody(), true);
                $assistantResponse = $result['choices'][0]['message']['content'];
                dump($prompt->id . ') ' . $this->removeQuotes($assistantResponse));
                $prompt->nameUa = $this->removeQuotes($assistantResponse);
//                $prompt->description = $assistantResponse;
                $prompt->save();
                sleep(2);
            }
        }
    }


    private function translateAi()
    {
    }

}
