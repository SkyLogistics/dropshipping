<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\OptionForProduct;
use App\Models\OrigamiProducts;
use App\Models\Product;
use App\Models\ProductOption;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Collection;
use PhpOffice\PhpSpreadsheet\IOFactory;

class DropService
{

    public function __construct()
    {
        $this->productService = new ProductService();
        $this->guzzle = new Client();
    }

    private function getCategory($name): string
    {
        if (str_contains($name, 'номерам')) {
            $category = "Картины по номерам";
        } elseif (str_contains($name, 'Алмазна')) {
            $category = "Алмазная мозаика";
        } else {
            $category = "Творчество";
        }

        return $category;
    }

    private function getProducts($products): array
    {
        dd(count($products));
        $excelData = [];
        foreach ($products as $row) {
            $royalProductsVendorCode[] = $row->vendorCode;
            if ($row->title == '' && $row->title_ua == '') {
                continue;
            }
            $parcelArrayInfo = [];
            $parcelArrayInfo[] = $row->vendorCode;
            $parcelArrayInfo[] = $row->title;
            $parcelArrayInfo[] = $row->title_ua;
            $parcelArrayInfo[] = $row->keywords;

            $row->keywordsUa = str_replace("&quot;", '"', $row->keywordsUa);
            $row->keywordsUa = str_replace("  ", ' ', $row->keywordsUa);

            $row->keywords = str_replace("&quot;", '"', $row->keywords);
            $row->keywords = str_replace("  ", ' ', $row->keywords);

            $parcelArrayInfo[] = $row->keywordsUa;
            //dd($row->description . '<p>' . $row->properties . '</p>');
            $parcelArrayInfo[] = $row->description;
            $parcelArrayInfo[] = $row->description_ua;

            $parcelArrayInfo[] = '';
            $percent = 70;
            $multiplier = 1 + ($percent / 100);
            $recommendedPrice = ceil($row->price * $multiplier);
            $row->recommendedPrice = $recommendedPrice;

            $parcelArrayInfo[] = $recommendedPrice;
            $parcelArrayInfo[] = 'UAH';
            $parcelArrayInfo[] = 'шт.';
            $parcelArrayInfo[] = '';
            $parcelArrayInfo[] = '';
            $parcelArrayInfo[] = '';

            $photoUrls = [];
            foreach ($row->imageUrl as $photo) {
                $photoUrls[] = $photo;
            }

            $parcelArrayInfo[] = implode(',', $photoUrls);
            $quantityInStock = 0;
            if ($row->quantityInStock > 0) {
                $quantityInStock = $row->quantityInStock;
                $parcelArrayInfo[] = '+';
            } else {
                $parcelArrayInfo[] = '-';
            }

            $parcelArrayInfo[] = $quantityInStock;
//            $categoryName = $this->getCategory($row->name);
//            $groupId = '';
//            if ($categoryName == 'Картины по номерам') {
//                $groupId = 118820562;
//            } elseif ($categoryName == 'Алмазная мозаика') {
//                $groupId = 118981497;
//            }
            $parcelArrayInfo[] = '';
            //dd($row->id.') '.$row->name.' -> '.$this->getCategory($row->name));
            $parcelArrayInfo[] = '';
            $parcelArrayInfo[] = '';
            $parcelArrayInfo[] = '';
            $parcelArrayInfo[] = '';
            $parcelArrayInfo[] = '';
            $parcelArrayInfo[] = '';
            $parcelArrayInfo[] = sprintf('%05d', $row->id);
            $parcelArrayInfo[] = $row->vendorCode;
            $parcelArrayInfo[] = '';
            $parcelArrayInfo[] = '';
            if ($row->vendor == "Рів'єра") {
                $vendor = 'Riviera';
            } elseif ($row->vendor == "Орігамі") {
                $vendor = 'Origami';
            } else {
                $vendor = $row->vendor;
            }

            $parcelArrayInfo[] = $vendor;
            $parcelArrayInfo[] = 'Украина';
            $parcelArrayInfo[] = '';
            $parcelArrayInfo[] = '';
            $parcelArrayInfo[] = '';
            $parcelArrayInfo[] = '';
            $parcelArrayInfo[] = '';
            $parcelArrayInfo[] = '';
            $parcelArrayInfo[] = '';
            $parcelArrayInfo[] = '';
            $parcelArrayInfo[] = '';
            $parcelArrayInfo[] = '';
            $parcelArrayInfo[] = '';
            $parcelArrayInfo[] = '';

            $row->keywords = str_replace("&quot;", '"', $row->keywords);
            $row->keywords = str_replace("  ", ' ', $row->keywords);

            /** @var OrigamiProducts $row */
            if ($row->isDirty()) {
                $row->save();
            }
            $categoryName = '';

            $parcelArrayInfo[] = mb_strtolower($row->keywords);
            $parcelArrayInfo[] = mb_strtolower($row->keywordsUa);
            $parcelArrayInfo[] = '';
            $parcelArrayInfo[] = '';

            $length = ProductOption::find(22);
            $width = ProductOption::find(23);
            $height = ProductOption::find(24);
            $weight = ProductOption::find(44);

            $length = OptionForProduct::query()
                ->where('option_id', $length->id)
                ->where('product_id', $row->id)->first();

            $width = OptionForProduct::query()
                ->where('option_id', $width->id)
                ->where('product_id', $row->id)->first();

            $height = OptionForProduct::query()
                ->where('option_id', $height->id)
                ->where('product_id', $row->id)->first();

            $weight = OptionForProduct::query()
                ->where('option_id', $weight->id)
                ->where('product_id', $row->id)->first();

            $le = 0;
            if ($length) {
                $le = $length->value;
            }
            $wi = 0;
            if ($width) {
                $wi = $width->value;
            }
            $he = 0;
            if ($height) {
                $he = $height->value;
            }

            $we = 0;
            if ($weight) {
                $we = $weight->value;
            }

            $parcelArrayInfo[] = floatval($we);
            $parcelArrayInfo[] = floatval($wi);
            $parcelArrayInfo[] = floatval($he);
            $parcelArrayInfo[] = floatval($le);
            $parcelArrayInfo[] = 'Київ';
            $options = $this->getProductOptions($row->id, [22, 23, 24, 44]);
            $optionsUa = $options->filter(function ($option) {
                return $option->lang == 'ua';
            });

            foreach ($optionsUa as $optionUa) {
                $parcelArrayInfo[] = $optionUa['title'];
                $parcelArrayInfo[] = '';
                $parcelArrayInfo[] = $optionUa['value'];
            }

            $excelData[] = $parcelArrayInfo;
        }

        return $excelData;
    }

    public function getExcelData(): array
    {
        $products = Product::query()
            ->where('active', 1)
            ->where('provider', 'royal')
            ->get();

        return $this->getProducts(
            $products
        );
    }

    private function translate($toLang, $text)
    {
//        $apiKey = 'AIzaSyCqrmsw4xk4KA1qa8cHtFYU7ShKAGbpBGE';
//        $response = $this->guzzle->post('https://translation.googleapis.com/language/translate/v2', [
//            'query' => [
//                'key' => $apiKey,
//            ],
//            'json' => [
//                'q' => $text,
//                'target' => $toLang,
//            ],
//        ]);

//        $result = json_decode($response->getBody(), true);
//
//        return $result['data']['translations'][0]['translatedText'];
        return '';
    }

    public function getImportFiles(string $directoryPath): ?array
    {
        if (!is_dir($directoryPath)) {
            return null;
        }
        return array_diff(scandir($directoryPath), array('.', '..'));
    }

    const CATEGORIES = [
        'doski-dlja-risovanija' => 'Доски для рисования',
        'golovolomki' => 'Головоломки',
        'kartiny-po-nomeram' => 'Картины по номерам',
        'konstruktory' => 'Конструкторы',
        'nabory-dlja-tvorchestva' => 'Наборы для творчества',
        'razvivajuschie-igrushki' => 'Развивающие игрушки',
        'shkolnaya-i-detskaya-kantselyariya' => 'Школа',
    ];

    public function getCategoryIdBySlug($slug)
    {
        $category = Category::query()->where('slug', $slug)->first();
        if (!$category) {
            $categoryTitle = self::CATEGORIES[$slug];
            $findCatId = Category::query()->create(
                [
                    'title' => $categoryTitle,
                    'title_ua' => '',
                    'slug' => $slug,
                    'summary' => '',
                    'photo' => '',
                    'status' => 'active',
                    'is_parent' => 0,
                    'parent_id' => null,
                ]
            );
        } else {
            $findCatId = $category->id;
        }

        return $findCatId;
    }

    public function getBrandIdBySlug($slug)
    {
        $brand = Brand::query()->where('slug', $slug)->first();
        if (!$brand) {
            $brandId = Brand::query()->create(
                [
                    'title' => $slug,
                    'title_ua' => $slug,
                    'slug' => $slug,
                    'summary' => '',
                    'photo' => '',
                    'status' => 'active',
                    'is_parent' => 0,
                    'parent_id' => null,
                ]
            );
        } else {
            $brandId = $brand->id;
        }

        return $brandId;
    }

    public function getRemoteData($provider, $file): array
    {
        $data = [];
        $categoryId = $this->getCategoryIdBySlug(pathinfo($file, PATHINFO_FILENAME));

        if ($provider == 'royal') {
            $filePath = storage_path() . '/app/public/royal/';
            $localFilePath = $filePath . $file;
            $spreadsheet = IOFactory::load($localFilePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $highestRow = $worksheet->getHighestRow();
            $highestColumn = $worksheet->getHighestColumn();

            for ($row = 1; $row <= $highestRow; ++$row) {
                $newRow = [];
                for ($col = 'A'; $col <= $highestColumn; ++$col) {
                    $cellValue = $worksheet->getCell($col . $row)->getValue();
                    echo $cellValue . ' ';
                    $newRow[] = $cellValue;
                }
                $data[] = $newRow;
                echo PHP_EOL;
            }
        } else {
            echo 'need correct provider';
            exit();
        }

        $dataResult = [];
        foreach ($data as $key => $item) {
            echo '$key = ' . $key . PHP_EOL;
            if ($key < 9) {
                continue;
            }

            if (!is_numeric($item[7])) {
                continue;
            }
            $vendor = $item[1];
            $vendorCode = $item[2];
            $imageUrl = $item[9];
            $productUrl = $item[10];
            $nameUa = '';
            $name = $item[4];
            $productType = '';
            $size = '';
            $price = $item[7];
            $increasePercentage = 20;
            $recommendedPrice = ceil($price * (1 + ($increasePercentage / 100)));
            $quantityInStock = 100;
            if (is_numeric($item[5])) {
                $quantityInStock = $item[5];
            }

            $brandId = $this->getBrandIdBySlug(trim($vendor));

            $dataResult[] = [
                'vendorCode' => trim($vendorCode),
                'vendor' => trim($vendor),
                'imageUrl' => $imageUrl,
                'productType' => $productType,
                'size' => $size,
                'price' => $price,
                'recommendedPrice' => $recommendedPrice,
                'quantityInStock' => $quantityInStock,
                'hasHigherPrice' => '',
                'active' => 0,
                'provider' => $provider,
                'productUrl' => $productUrl,
                'title' => $name,
                'title_ua' => $nameUa,
                'summary' => '',
                'description' => '',
                'description_ua' => '',
                'photo' => $imageUrl,
                'stock' => 0,
                'cat_id' => $categoryId,
                'brand_id' => $brandId,
                'child_cat_id' => null,
                'is_featured' => null,
                'status' => 'active,inactive',
                'condition' => 'default',
                'discount' => 0,
            ];
        }

        //$data = file_get_contents($url);
        //TODO: parse data
        return $dataResult;
    }

    public function getProductOptions($productId, $notOption): Collection|array
    {
        return OrigamiProducts::query()
            ->join('option_for_product', 'origami_product.id', '=', 'option_for_product.product_id')
            ->join('product_option', 'product_option.id', '=', 'option_for_product.option_id')
            ->select(
                'origami_product.id as productId',
                'product_option.title as title',
                'option_for_product.value as value',
                'product_option.lang as lang'
            )->where('origami_product.id', $productId)
            ->whereNotIn('product_option.id', $notOption)
            ->get();
    }
}
