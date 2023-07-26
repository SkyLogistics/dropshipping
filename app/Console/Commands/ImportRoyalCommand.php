<?php

namespace App\Console\Commands;

use App\Models\Category;
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
        $dir = storage_path("app/public/$inputKey/xml");
        Product::query()->where('id', '>', 20)->delete();
        Category::query()->where('id', '>', 20)->delete();

        $pathFiles = $this->dropService->getImportFiles($dir);
        $dir = storage_path("app/public/$inputKey");

        foreach ($pathFiles as $pathFile) {
            $lang = substr($pathFile, 0, 2);
            $file = $dir . '/xml/' . $pathFile;
            $xmlObject = simplexml_load_file($file);
            $categories = $xmlObject->shop->categories->category;
            $offers = $xmlObject->shop->offers->offer;
            foreach ($categories as $category) {
                $categoryId = json_decode(json_encode($category['id']), true)[0];
                $parentCategoryId = isset($category['parentId']) ? json_decode(
                    json_encode($category['parentId']),
                    true
                )[0] : null;
                $categoryName = (string)$category;
                $title = 'title';
                if ($lang != 'ru') {
                    $title = 'title_ua';
                }
                $create = [
                    'cat_id' => $categoryId,
                    'parent_id' => is_null($parentCategoryId) ? null : $parentCategoryId,
                    'is_parent' => is_null($parentCategoryId) ? 1 : 0,
                    $title => $categoryName,
                    'status' => 'inactive',
                ];

                $findCat = Category::query()
                    ->where('cat_id', $categoryId)
                    ->first();

                if ($findCat) {
                    if ($lang == 'ua') {
                        $findCat->title_ua = $categoryName;
                    }
                    $findCat->save();
                } else {
                    if (!is_null($parentCategoryId)) {
                        $catParent = Category::query()
                            ->where('cat_id', $parentCategoryId)->first();
                        $create['parent_id'] = $catParent->id;
                    }
                    $create['slug'] = $this->transliterateRussianToLatin($categoryName);
                    $findCat = Category::query()->create($create);
                }
                dump($findCat->title);
            }

            foreach ($offers as $offer) {
                $percent = 70;
                $multiplier = 1 + ($percent / 100);
                $recommendedPrice = ceil((double)$offer->price * $multiplier);
                $quantityInStock = (integer)$offer->stock_quantity;
                $vendorCode = (string)$offer->vendorCode;
                $catId = (string)$offer->categoryId;

                $myCat = Category::query()
                    ->where('cat_id', $catId)->first();

                $categoryByCatId = Product::query()
                    ->where('cat_id', $myCat->id)
                    ->first();

                if ($categoryByCatId) {
                    $myCat->status = 'active';
                    $myCat->save();

                    $myCat = Category::query()
                        ->where('id', $myCat->parent_id)->first();
                    if ($myCat) {
                        $myCat->status = 'active';
                        $myCat->save();
                    }
                }

                $myOffer = [
                    'art_id' => (string)json_decode(json_encode($offer['id']), true)[0],
                    'vendorCode' => $vendorCode,
                    'vendor' => (string)$offer->vendor,
                    'slug' => $this->transliterateRussianToLatin((string)$offer->name),
                    'imageUrl' => $offer->picture,
                    'title' => (string)$offer->name,
                    'description' => (string)$offer->description,
                    'productType' => '',
                    'size' => '',
                    'price' => (double)$offer->price,
                    'recommendedPrice' => $recommendedPrice,
                    'quantityInStock' => $quantityInStock,
                    'hasHigherPrice' => '',
                    'active' => 1,
                    'provider' => 'royal',
                    'productUrl' => (string)$offer->url,
                    'summary' => '',
                    'photo' => '',
                    'stock' => ($quantityInStock > 0) ? 1 : 0,
                    'cat_id' => $myCat->id,
                    'brand_id' => null,
                    'child_cat_id' => null,
                    'is_featured' => 0,
                    'status' => 'active',
                    'condition' => 'default',
                    'discount' => 0,
                ];

                $product = Product::query()->where('vendorCode', $vendorCode)->first();
                if ($product) {
                    $product->title_ua = (string)$offer->name;
                    $product->description_ua = (string)$offer->description;
                    $product->save();
                } else {
                    Product::query()
                        ->create($myOffer);
                }
                //dd($product);
            }
        }


//        for ($i = 0; $i < 20; $i++) {
//            dump($array[$i]);
//        }
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
