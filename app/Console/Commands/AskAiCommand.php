<?php

namespace App\Console\Commands;

use App\Services\DropService;
use Illuminate\Console\Command;
use OpenAI\Client;
use OpenAI\API\API;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

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
        $yourApiKey = getenv('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');

        $client = \OpenAI::factory()
            ->withApiKey($yourApiKey)
            ->withOrganization('ArtNum') // default: null
            ->withBaseUri('api.openai.com/v1/completions') // default: api.openai.com/v1
            ->withHttpClient(
                $client = new \GuzzleHttp\Client([])
            ) // default: HTTP client found using PSR-18 HTTP Client Discovery
            ->withHttpHeader('Authorization', 'Bearer ' . $yourApiKey)
            ->withHttpHeader('OpenAI-Organization', 'org-WYlAZmxU71900K2AUOdRsw3e')
            ->withStreamHandler(fn(RequestInterface $request): ResponseInterface => $client->send($request, [
                'stream' => true // Allows to provide a custom stream handler for the http client.
            ]))
            ->make();

        $stream = $client->completions()->createStreamed(
            [
                'model' => 'text-davinci-003',
                'prompt' => 'Php is ',
                'max_tokens' => 10,
            ]
        );

        foreach ($stream as $response) {
            $this->info($response->choices[0]->text);
        }
    }

    private function translateAi()
    {
    }

}
