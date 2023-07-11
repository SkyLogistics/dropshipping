<?php

namespace App\Console\Commands;

use App\Models\OrigamiProducts;
use App\Services\DropService;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class UpdateProductOptionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update_options';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update options';

    private DropService $dropService;

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
        $products = OrigamiProducts::query()
            ->whereNotNull('options')
            ->get();

        foreach ($products as $product){
            $optionRu = json_decode($product->options, true);
            dd($optionRu);
            $optionUa = json_decode($product->options_ua, true);
        }

    }
}
