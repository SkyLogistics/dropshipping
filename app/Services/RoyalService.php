<?php

namespace App\Services;

use App\Models\OptionForProduct;
use App\Models\OrigamiProducts;
use App\Models\ProductOption;
use App\Models\TmpAvizationScanned;
use App\Models\TmpAvizationSelected;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\Eloquent\Collection;
use JetBrains\PhpStorm\ArrayShape;
use PhpOffice\PhpSpreadsheet\IOFactory;

class RoyalService
{
    /**
     * @var Client
     */
    private $guzzle;

    public function __construct()
    {
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

    #[ArrayShape(['vendorCodes' => "array", 'excelData' => "array"])]
    private function getProducts($products): array
    {
        $royalProductsIds = [];
        $excelData = [];
        foreach ($products as $row) {
            $royalProductsVendorCode[] = $row->vendorCode;
            if ($row->nameUa == '' && $row->name == '') {
                continue;
            }
            $parcelArrayInfo = [];
            $parcelArrayInfo[] = $row->vendorCode;
            if ($row->name == '' && $row->nameUa != '') {
                $text = $row->nameUa;
                $row->name = str_replace("&quot;", '"', $text);
                $row->name = str_replace("  ", ' ', $row->name);
            }

            $parcelArrayInfo[] = $row->name;
            $parcelArrayInfo[] = $row->nameUa;
            $parcelArrayInfo[] = $row->keywords;
            $row->keywordsUa = str_replace("&quot;", '"', $row->keywordsUa);
            $row->keywordsUa = str_replace("  ", ' ', $row->keywordsUa);
            $parcelArrayInfo[] = $row->keywordsUa;
            $parcelArrayInfo[] = $row->description;
            $parcelArrayInfo[] = $row->description_ua;

            if ($row['vendorCode'] == 'ART_AL001') {
                $productType = 'Акриловий лак';
            } else {
                if ($row['productType'] == 'Інше') {
                    $productType = 'Розмальовка  для дітей';
                } else {
                    $productType = $row->productType;
                }
            }

            $parcelArrayInfo[] = $productType;
            $recommendedPrice = $row->recommendedPrice;

            if ($row->price > $row->recommendedPrice) {
                $recommendedPrice = $row->price;
            }

            if ($row->provider == 'royal') {
                $percent = 70;
                $multiplier = 1 + ($percent / 100);
                $recommendedPrice = ceil($row->price * $multiplier);
                $row->recommendedPrice = $recommendedPrice;
            }

            $parcelArrayInfo[] = $recommendedPrice;
            $parcelArrayInfo[] = 'UAH';
            $parcelArrayInfo[] = 'шт.';
            $parcelArrayInfo[] = '';
            $parcelArrayInfo[] = '';
            $parcelArrayInfo[] = '';
            $parcelArrayInfo[] = $row->imageUrl;
            $quantityInStock = 0;
            if ($row->quantityInStock > 0) {
                $quantityInStock = $row->quantityInStock;
                $parcelArrayInfo[] = '+';
            } else {
                $parcelArrayInfo[] = '-';
            }

            $parcelArrayInfo[] = $quantityInStock;
            $categoryName = $this->getCategory($row->name);
            $groupId = '';
            if ($categoryName == 'Картины по номерам') {
                $groupId = 118820562;
            } elseif ($categoryName == 'Алмазная мозаика') {
                $groupId = 118981497;
            }
            $parcelArrayInfo[] = $groupId;
            //dd($row->id.') '.$row->name.' -> '.$this->getCategory($row->name));
            $parcelArrayInfo[] = $categoryName;
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

            $parcelArrayInfo[] = $categoryName . ',' . mb_strtolower($row->keywords);
            $parcelArrayInfo[] = $categoryName . ',' . mb_strtolower($row->keywordsUa);
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

        return ['vendorCodes' => $royalProductsIds, 'excelData' => $excelData];
    }

    public function getExcelData(): array
    {
        $products = OrigamiProducts::query()
            ->where('active', 1);

        $productsRoyal = $products
            ->where('provider', 'royal')
            ->get();
        $royalProductsExcelData = $this->getProducts($productsRoyal);

//        $productsOrigami = $products
//            ->whereNotIn('vendorCode', $royalProductsExcelData['vendorCodes'])
//            ->get();
//        $origamiProductsExcelData = $this->getProducts($productsOrigami);


        return array_merge($royalProductsExcelData['excelData'], []);
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

    /**
     * @throws GuzzleException
     */
    public function getRemoteData(string $url, $provider): array
    {
        $data = [];
        if ($provider == 'origami') {
            $data = json_decode(
                $this->guzzle->get(
                    $url, []
                )->getBody()->getContents(),
                true
            );
        } elseif ($provider == 'royal') {
            $filePath = storage_path() . '/app/public/royal/';
            $localFilePath = $filePath . 'kartiny-po-nomeram.xlsx';
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

            $name = '';
            $productUrl = '';
            if ($provider == 'origami') {
                $vendor = $item['vendor'];
                $vendorCode = trim($item['vendorCode']);
                $imageUrl = $item['imageUrl'];
                $nameUa = $item['nameUa'];
                $productType = $item['productType'];
                $size = $item['productType'];
                $price = $item['price'];
                $recommendedPrice = $item['recommendedPrice'];
                $quantityInStock = $item['quantityInStock'];
            } elseif ($provider == 'royal') {
                dump($item);
                //echo $item . PHP_EOL;
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
            } else {
                continue;
            }

            $dataResult[] = [
                'vendorCode' => trim($vendorCode),
                'vendor' => trim($vendor),
                'imageUrl' => $imageUrl,
                'nameUa' => $nameUa,
                'name' => $name,
                'promID' => '',
                'description' => '',
                'description_ua' => '',
                'productType' => $productType,
                'size' => $size,
                'price' => $price,
                'recommendedPrice' => $recommendedPrice,
                'quantityInStock' => $quantityInStock,
                'hasHigherPrice' => '',
                'active' => 0,
                'provider' => $provider,
                'productUrl' => $productUrl,
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
