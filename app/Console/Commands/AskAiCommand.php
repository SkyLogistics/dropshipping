<?php

namespace App\Console\Commands;

use App\Services\DropService;
use Illuminate\Console\Command;
use Orhanerday\OpenAi\OpenAi;

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

    public function handle(): void
    {
        $yourApiKey = config('app.open_ai');
//        $transport = new RequestTranspo($yourApiKey);
//        $client = new Client($transport);
        $client = new OpenAi($yourApiKey);

        $complete = json_decode(
            $client->complete(
                [
                    'engine' => 'davinci',
                    'prompt' => "Напиши опис для картини по номерах - на передньому плані плавають два лебеді, доторкаючись один до одного клювами, утворюючи ніби серце. У воді відзеркалення лебедів та дерев, що є по боках на березі. На задньому плані романтичний міст. Це восени. Кожен абзац обрамити в тег <p>.",
                    'temperature' => 0.9,
                    'max_tokens' => 1700,
                    'frequency_penalty' => 0,
                    'presence_penalty' => 0.6,
                ]
            ),
            true
        );

        dd($complete);

//        $chat = json_decode(
//            $client->chat(
//                [
//                    'model' => 'gpt-3.5-turbo',
//                    'messages' => [
//                        [
//                            "role" => "user",
//                            "content" => "Будь копірайтером. Напиши опис для картини по номерах [опис картини: на передньому плані плавають два лебеді, доторкаючись один до одного клювами, утворюючи ніби серце. У воді видно відзеркалення лебедів та відзеркалення дерев, що є по боках на березі. На задньому плані картини видно міст, що дає змогу пернйти з одного берега на інший. Міст виглядає дуже романтично. Пора року, що зображена на картині - осінь.]для інтернет-магазину, який приверне увагу мого ідеального клієнта [опис клієнта: 20-45 років, жіноча стать, захоплення мистетством, рукоділлям, малюванням] сильним заголовком і зачіпкою, а потім переконає його зробити [покупку картини] за допомогою переконливої мови і переконливих доказів. Кожен абзац обрам в тег <p>."
//                        ],
//                    ],
//                    'temperature' => 1.0,
//                    'max_tokens' => 4000,
//                    'frequency_penalty' => 0,
//                    'presence_penalty' => 0,
//                ]
//            ),
//            true
//        );

        dd($chat);

        $assistantResponse = $chat['choices'][0]['message']['content'];

        dd($assistantResponse);

//        $result = $client->chatCompletions()->create($data);
//
//        $assistantResponse = $result['choices'][0]['message']['content']; // Extract the assistant's response


        //dd($yourApiKey);
//        $model = 'gpt-3.5-turbo';
//        $url = 'https://api.openai.com/v1/engines/' . $model . '/completions';
//
//        $data = [
//            "prompt" => ["Будь копірайтером. Напиши опис для картини по номерах [опис картини: на передньому плані плавають два лебеді, доторкаючись один до одного клювами, утворюючи ніби серце. У воді видно відзеркалення лебедів та відзеркалення дерев, що є по боках на березі. На задньому плані картини видно міст, що дає змогу пернйти з одного берега на інший. Міст виглядає дуже романтично. Пора року, що зображена на картині - осінь.]для інтернет-магазину, який приверне увагу мого ідеального клієнта [опис клієнта: 20-45 років, жіноча стать, захоплення мистетством, рукоділлям, малюванням] сильним заголовком і зачіпкою, а потім переконає його зробити [покупку картини] за допомогою переконливої мови і переконливих доказів. Кожен абзац обрам в тег <p>.",
//            'temperature' => 0.7,
//            'max_tokens' => 1500,
//            'stream' => false,
//        ];
//
//        $headers = [
//            'Content-Type: application/json',
//            'Authorization: Bearer ' . $yourApiKey,
//        ];
//
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, $url);
//        curl_setopt($ch, CURLOPT_POST, 1);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
//        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//
//        $response = curl_exec($ch);
//        curl_close($ch);
//
//        $result = json_decode($response, true);
//        dd($result);
//        //echo $result['choices'][0]['text']; // Output the generated text
//
//
//

//        $yourApiKey = getenv('YOUR_API_KEY');
//        $client = OpenAI::client($yourApiKey);
//
//        $result = $client->completions()->create([
//                                                     'model' => 'text-davinci-003',
//                                                     'prompt' => 'PHP is',
//                                                 ]);
//
//        echo $result['choices'][0]['text'];
//
//        //dd($yourApiKey);
//        $client = \OpenAI::client($yourApiKey);

//        $result = $client->completions()->create(
//            [
//                "model" => "gpt-3.5-turbo",
////                'prompt' => "Будь копірайтером. Напиши опис для картини по номерах [опис картини: на передньому плані плавають два лебеді, доторкаючись один до одного клювами, утворюючи ніби серце. У воді видно відзеркалення лебедів та відзеркалення дерев, що є по боках на березі. На задньому плані картини видно міст, що дає змогу пернйти з одного берега на інший. Міст виглядає дуже романтично. Пора року, що зображена на картині - осінь.]для інтернет-магазину, який приверне увагу мого ідеального клієнта [опис клієнта: 20-45 років, жіноча стать, захоплення мистетством, рукоділлям, малюванням] сильним заголовком і зачіпкою, а потім переконає його зробити [покупку картини] за допомогою переконливої мови і переконливих доказів. Кожен абзац обрам в тег <p>.",
//                "messages" => "Будь копірайтером. Напиши опис для картини по номерах [опис картини: на передньому плані плавають два лебеді, доторкаючись один до одного клювами, утворюючи ніби серце. У воді видно відзеркалення лебедів та відзеркалення дерев, що є по боках на березі. На задньому плані картини видно міст, що дає змогу пернйти з одного берега на інший. Міст виглядає дуже романтично. Пора року, що зображена на картині - осінь.]для інтернет-магазину, який приверне увагу мого ідеального клієнта [опис клієнта: 20-45 років, жіноча стать, захоплення мистетством, рукоділлям, малюванням] сильним заголовком і зачіпкою, а потім переконає його зробити [покупку картини] за допомогою переконливої мови і переконливих доказів. Кожен абзац обрам в тег <p>.",
//                "temperature" => 0.7,
////                'max_tokens' => 1300,
//            ]
//        );

        dd($result);

        echo $result['choices'][0]['text'];
    }

    private function translateAi()
    {
    }

}
