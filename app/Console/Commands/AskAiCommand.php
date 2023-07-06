<?php

namespace App\Console\Commands;

use App\Services\DropService;
use Illuminate\Console\Command;
use OpenAI\Client;
use OpenAI\API\API;

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
        $openai = new API('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');
        $result = $openai->completions()->create(
            [
                'model' => 'text-davinci-003',
                'prompt' => 'PHP is',
            ]
        );

        echo $result['choices'][0]['text'];
    }

    private function translateAi()
    {
    }

}
