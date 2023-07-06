<?php

namespace App\Console\Commands;

use App\Models\OrigamiProducts;
use App\Services\DropService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class AskAiCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-ask}';

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
        $client = \OpenAI::client($yourApiKey);

        $result = $client->completions()
            ->create([
                         'model' => 'text-davinci-003',
                         'prompt' => 'PHP is',
                     ]);

        echo $result['choices'][0]['text'];
    }

    private function translateAi()
    {
    }

}
