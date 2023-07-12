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
            ->where('product_id', '>=', 32468)
            ->get();

        $i = 1;
        foreach ($products as $product) {
            $optionRu = json_decode($product->options, true);
            $optionUa = json_decode($product->options_ua, true);
            $options = array_merge($optionRu, $optionUa);
            $j = 1;
            foreach ($options as $option) {
                if (
                    $option['title'] == 'Рекомендованная цена' ||
                    $option['title'] == 'Рекомендована ціна' ||
                    $option['title'] == '') {
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

                $this->line($i . ' -> ' . $j . ') ' . 'findOptId = ' . $findOpt->id . ' -> ' . $findOpt->title);

                //$product->options()->attach($findOpt->id);

//                dd($product->options());

////                $product->options()->value = $option['value'];
//
                $findOptForProduct = OptionForProduct::query()
                    ->where('option_id', $findOpt->id)
                    ->where('product_id', $product->id)
                    ->where('value', $option['value'])->first();
                if (!$findOptForProduct) {
                    OptionForProduct::query()
                        ->create([
                                     'option_id' => $findOpt->id,
                                     'product_id' => $product->id,
                                     'value' => $option['value'],
                                 ]);
//                    $product->options()->attach($option->id);
                }
                $j++;
            }
            //dd($findOptForProduct);
            $i++;
        }
    }
}
