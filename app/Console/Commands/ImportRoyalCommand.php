<?php

namespace App\Console\Commands;

use App\Models\OrigamiProducts;
use App\Models\Product;
use App\Services\DropService;
use App\Services\ProductService;
use Behat\Transliterator\Transliterator;
use Illuminate\Console\Command;

class ImportRoyalCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import {provider}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import royal xls file';

    private DropService $dropService;
    /**
     * @var ProductService
     */
    private $productService;

    /**
     * Create a new command instance.
     *
     */
    public function __construct(DropService $dropService, ProductService $productService)
    {
        $this->dropService = $dropService;
        $this->productService = $productService;
        parent::__construct();
    }

    public function handle(): void
    {
        $inputKey = $this->argument('provider');
        $dir = storage_path("app/public/$inputKey");
        $pathFiles = $this->dropService->getImportFiles($dir);
        $data = [];
        foreach ($pathFiles as $pathFile) {
            if (str_contains($pathFile, 'kartiny-po-nomeram')) {
                continue;
            }
            $data = array_merge($data, $this->dropService->getRemoteData($inputKey, $pathFile));
            dump($data);
        }

//        dd($data);
//        $template = 'export-origami.xls';

        foreach ($data as $apiProduct) {
            $vendor = $apiProduct['vendor'];
            $oneProduct = Product::query()
                ->where(
                    'vendorCode',
                    trim($apiProduct['vendorCode'])
                )
                ->where('provider', $inputKey)
                ->first();

            if ($oneProduct) {
                if ($apiProduct['vendorCode'] == 'AL001') {
                    $productType = 'Акриловий лак';
                } else {
                    if ($apiProduct['productType'] == 'Інше') {
                        $productType = 'Розмальовка  для дітей';
                    } else {
                        $productType = $apiProduct['productType'];
                    }
                }
                $recommendedPrice = $apiProduct['recommendedPrice'];
                if ($apiProduct['price'] > $apiProduct['recommendedPrice']) {
                    $recommendedPrice = $apiProduct['price'];
                }
                $oneProduct->provider = $inputKey;
                $oneProduct->price = $apiProduct['price'];
                $oneProduct->recommendedPrice = $recommendedPrice;
                $oneProduct->vendor = $vendor;
                $oneProduct->productType = $productType;
                $oneProduct->nameUa = str_replace("  ", ' ', $oneProduct->title);
                $oneProduct->nameUa = str_replace("  ", ' ', $oneProduct->title_ua);

                $oneProduct->name = str_replace("&quot;", '"', $apiProduct['title']);
                $oneProduct->name = str_replace("  ", ' ', $oneProduct->title);

                $oneProduct->vendorCode = trim($apiProduct['vendorCode']);
                $oneProduct->productUrl = $apiProduct['productUrl'];
                if ($oneProduct->title_ua != '' || $oneProduct->title != '') {
                    $oneProduct->active = 1;
                }
                $oneProduct->save();
            } else {
                $promId = 0;
                if (isset($apiProduct['promID'])) {
                    $promId = $apiProduct['promID'];
                }
                Product::query()
                    ->create(
                        [
                            'title' => str_replace(PHP_EOL, '', $apiProduct['title']),
                            'slug' => $this->transliterateRussianToLatin($apiProduct['title']),
                            'summary' => '',
                            'cat_id' => $apiProduct['cat_id'],
                            'child_cat_id' => '',
                            'brand_id' => null,
                            'discount' => 0,
                            'status' => '',
                            'photo' => '',
                            'stock' => null,
                            'is_featured' => 0,
                            'condition' => '',
                            'options' => '',
                            'active',
                            'vendorCode' => trim($apiProduct['vendorCode']),
                            'vendor' => trim($vendor),
                            'imageUrl' => $apiProduct['imageUrl'],
                            'title_ua' => '',
                            'promID' => $promId,
                            'description' => '',
                            'description_ua' => '',
                            'productType' => '',
                            'size' => $apiProduct['size'],
                            'price' => $apiProduct['price'],
                            'recommendedPrice' => $apiProduct['recommendedPrice'],
                            'quantityInStock' => $apiProduct['quantityInStock'],
                            'hasHigherPrice' => ($apiProduct['hasHigherPrice'] != '') ? $apiProduct['hasHigherPrice'] : false,
                            'active' => 0,
                            'provider' => $inputKey,
                            'productUrl' => $apiProduct['productUrl'],
                        ]
                    );
            }
        }
    }

    function transliterateRussianToLatin($input)
    {
        return Transliterator::transliterate($input);
    }
}
