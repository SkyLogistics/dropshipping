<?php

namespace App\Services;

use App\Models\OrigamiProducts;
use App\Models\TmpAvizationScanned;
use App\Models\TmpAvizationSelected;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use JetBrains\PhpStorm\ArrayShape;
use PhpOffice\PhpSpreadsheet\IOFactory;

class DropService
{
    /**
     * @var Client
     */
    private $guzzle;

    public function __construct()
    {
        $this->guzzle = new Client();
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
                $targetLanguage = 'ru';
                $translatedText = '';//$this->translate($targetLanguage, $text);
                $row->name = str_replace("&quot;", '"', $text);
                $row->name = str_replace("  ", ' ', $row->name);
            }

            $parcelArrayInfo[] = $row->name;
            $parcelArrayInfo[] = $row->nameUa;
            $parcelArrayInfo[] = 0;

            $row->keywordsUa = str_replace("&quot;", '"', $row->keywordsUa);
            $row->keywordsUa = str_replace("  ", ' ', $row->keywordsUa);

            $parcelArrayInfo[] = $row->keywordsUa;
            //dd($row->description . '<p>' . $row->properties . '</p>');
            $parcelArrayInfo[] = $row->description . '<p>' . $row->properties . '</p>';
            $parcelArrayInfo[] = $row->description_ua .'<p>' . $row->properties_ua . '</p>';

            if ($row['vendorCode'] == 'ART_AL001') {
                $productType = 'Акриловий лак';
            } else {
                if ($row['productType'] == 'Інше') {
                    $productType = 'Розмальовка  для дітей';
                } else {
                    $productType = $row->productType;
                }
            }

            $weight = '0.3';
            if ($productType == 'Алмазна мозаїка') {
                $weight = '1';
            }

            $parcelArrayInfo[] = $productType;
            $recommendedPrice = $row->recommendedPrice;

            if ($row->price > $row->recommendedPrice) {
                $recommendedPrice = $row->price;
            }

            if ($row->provider == 'royal') {
                $percent = 35;
                $multiplier = 1 + ($percent / 100);
                $recommendedPrice = $row->price * $multiplier;
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
            $parcelArrayInfo[] = '';
            $parcelArrayInfo[] = '';
            $parcelArrayInfo[] = '';
            $parcelArrayInfo[] = '';
            $parcelArrayInfo[] = '';
            $parcelArrayInfo[] = '';
            $parcelArrayInfo[] = '';
            $parcelArrayInfo[] = sprintf('%05d', $row['id']);
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

            if ($row->keywordsUa != '') {
                $targetLanguage = 'ru';
                $translatedText = '';//$this->translate($targetLanguage, $row->keywordsUa);
                $row->keywords = str_replace("&quot;", '"', $row->keywordsUa);
                $row->keywords = str_replace("  ", ' ', $row->keywords);
            }

            /** @var OrigamiProducts $row */
            if ($row->isDirty()) {
                $row->save();
            }

            $parcelArrayInfo[] = 'Картина по номерам,' . mb_strtolower($row->keywords);
            $parcelArrayInfo[] = 'Картина по номерах, ' . mb_strtolower($row->keywordsUa);
            $parcelArrayInfo[] = '';
            $parcelArrayInfo[] = '';
            $size = explode('*', $row->size);
            $width = 'Запитайте у менеджера сайту';
            $height = '';
            if (isset($size[0])) {
                $width = $size[0];
            }
            if (isset($size[1])) {
                $height = $size[1];
            }
            $parcelArrayInfo[] = $weight;
            $parcelArrayInfo[] = $height;
            $parcelArrayInfo[] = $width;
            $parcelArrayInfo[] = '1';
            $parcelArrayInfo[] = 'Київ/Дніпро';
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

    /**
     * @param array $data
     * @param $provider
     * @return array
     */
    public function getApiProduct(array $data, $provider): array
    {
        return '';
    }
}
