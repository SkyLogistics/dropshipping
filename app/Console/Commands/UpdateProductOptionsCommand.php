<?php

namespace App\Console\Commands;

use App\Models\OptionForProduct;
use App\Models\OrigamiProducts;
use App\Models\ProductOption;
use App\Services\DropService;
use Carbon\Carbon;
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

        foreach ($products as $product) {
            $optionRu = json_decode($product->options, true);
            $optionUa = json_decode($product->options_ua, true);
            $options = array_merge($optionRu, $optionUa);
            foreach ($options as $option) {
                if ($option['title'] == 'Рекомендованная цена' || ($option['title'] == 'Рекомендована ціна')) {
                    continue;
                }
                $findOpt = ProductOption::query()
                    ->where('title', $option['title'])
                    ->where('lang', $option['lang'])
                    ->first();

                if (!$findOpt) {
                    $findOpt = ProductOption::query()
                        ->create([
                                     'title' => $option['title'],
                                     'lang' => $option['lang'],
                                     'created_at' => Carbon::now(),
                                     'updated_at' => Carbon::now(),
                                 ]);
                }

                OptionForProduct::query()
                    ->create([
                                 'option_id' => $findOpt->id,
                                 'product_id' => $product->id,
                                 'value' => $option->value,
                             ]);
            }
        }
    }
}
