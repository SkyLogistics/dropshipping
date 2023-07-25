<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\DropService;
use App\Services\ProductService;
use Behat\Transliterator\Transliterator;
use Illuminate\Console\Command;

class ImportRoyalUrlCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import_url {provider}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import royal xls file';

    private DropService $dropService;

    /**
     * Create a new command instance.
     *
     */
    public function __construct(DropService $dropService, ProductService $productService)
    {
        $this->dropService = $dropService;
        parent::__construct();
    }

    public function handle(): void
    {
        $inputKey = $this->argument('provider');
        $dir = storage_path("app/public/$inputKey");
        $url = $this->dropService->getImportFiles($dir);
        $data = [];
        Product::query()->where('id', '>',20)->delete();
        $url = '';
//        foreach ($pathFiles as $pathFile) {
//            if (str_contains($pathFile, 'kartiny-po-nomeram')) {
//                continue;
//            }
//            $data = array_merge($data, $this->dropService->getRemoteData($inputKey, $pathFile));
//            dump($data);
//        }

//        foreach ($data as $apiProduct) {
//            $vendor = $apiProduct['vendor'];
//            $oneProduct = Product::query()
//                ->where(
//                    'vendorCode',
//                    trim($apiProduct['vendorCode'])
//                )
//                ->where('provider', $inputKey)
//                ->first();
//            $brandId = $this->dropService->getBrandIdBySlug(trim($vendor));
//            if ($oneProduct) {
//                $recommendedPrice = $apiProduct['recommendedPrice'];
//                if ($apiProduct['price'] > $apiProduct['recommendedPrice']) {
//                    $recommendedPrice = $apiProduct['price'];
//                }
//                $oneProduct->provider = $inputKey;
//                dump($oneProduct->provider);
//                $oneProduct->price = $apiProduct['price'];
//                $oneProduct->brand_id = $brandId;
//                $oneProduct->recommendedPrice = $recommendedPrice;
//                $oneProduct->vendor = $vendor;
//                $oneProduct->title = str_replace("&quot;", '"', $apiProduct['title']);
//                $oneProduct->title = str_replace("  ", ' ', $oneProduct->title);
//
//                $oneProduct->vendorCode = trim($apiProduct['vendorCode']);
//                $oneProduct->productUrl = $apiProduct['productUrl'];
//                if ($oneProduct->title_ua != '' || $oneProduct->title != '') {
//                    $oneProduct->active = 1;
//                }
//                $oneProduct->save();
//                //dd($oneProduct);
//            } else {
//                $promId = 0;
//                if (isset($apiProduct['promID'])) {
//                    $promId = $apiProduct['promID'];
//                }
//                $product = Product::query()
//                    ->create(
//                        [
//                            'title' => str_replace(PHP_EOL, '', $apiProduct['title']),
//                            'slug' => $this->transliterateRussianToLatin($apiProduct['title']),
//                            'summary' => '',
//                            'cat_id' => $apiProduct['cat_id'],
//                            'child_cat_id' => null,
//                            'brand_id' => $brandId,
//                            'discount' => 0,
//                            'photo' => '',
//                            'stock' => 1,
//                            'status' => 'active',
//                            'is_featured' => 0,
//                            'condition' => 'default',
//                            'options' => '',
//                            'active',
//                            'vendorCode' => trim($apiProduct['vendorCode']),
//                            'vendor' => trim($vendor),
//                            'imageUrl' => $apiProduct['imageUrl'],
//                            'title_ua' => '',
//                            'promID' => $promId,
//                            'description' => '',
//                            'description_ua' => '',
//                            'productType' => '',
//                            'size' => $apiProduct['size'],
//                            'price' => $apiProduct['price'],
//                            'recommendedPrice' => $apiProduct['recommendedPrice'],
//                            'quantityInStock' => $apiProduct['quantityInStock'],
//                            'hasHigherPrice' => ($apiProduct['hasHigherPrice'] != '') ? $apiProduct['hasHigherPrice'] : false,
//                            'active' => 0,
//                            'provider' => $inputKey,
//                            'productUrl' => $apiProduct['productUrl'],
//                        ]
//                    );
//                dump($product->id);
//            }
//        }
    }

    function transliterateRussianToLatin($input)
    {
        return Transliterator::transliterate($input);
    }
}
