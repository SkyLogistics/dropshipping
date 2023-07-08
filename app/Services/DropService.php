<?php

namespace App\Services;

use App\Models\OrigamiProducts;
use App\Models\TmpAvizationScanned;
use App\Models\TmpAvizationSelected;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;

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

    public function updateProm(...$params)
    {
        dd($params);
    }

    public function getExcelData($provider): array
    {
        $excelData = [];
        $products = OrigamiProducts::query()
            ->where('active', 1)
            ->where('provider', $provider)
            ->get();


        $column = 'vendorCode';
        $priceColumn = 'price';
        $table = 'origami_product';

        $duplicates = DB::table($table)
            ->select($table . '.*')
            ->join(
                DB::raw(
                    '(SELECT ' . $column . ', MIN(' . $priceColumn . ') AS min_price FROM ' . $table . ' GROUP BY ' . $column . ' HAVING COUNT(*) > 1) duplicates'
                ),
                function ($join) use ($column, $table) {
                    $join->on($table . '.' . $column, '=', 'duplicates.' . $column);
                }
            )
            ->orderBy($column)
            ->get();

        foreach ($duplicates as $duplicate) {
            echo 'id = ' . $duplicate->id . ' ==> ' . $duplicate->$column . ' - Min Price: ' . $duplicate->$priceColumn . PHP_EOL;
        }

        dd($duplicates);

        foreach ($products as $row) {
            if ($row->nameUa == '' && $row->name == '') {
                continue;
            }
            $parcelArrayInfo = [];
            $parcelArrayInfo[] = $row->vendorCode;
            if ($row->name == '' && $row->nameUa != '') {
                $text = $row->nameUa;
                $targetLanguage = 'ru';
                $translatedText = $this->translate($targetLanguage, $text);
                $row->name = str_replace("&quot;", '"', $translatedText);
                $row->name = str_replace("  ", ' ', $row->name);
            }

            //$this->line($row->id . '. ' . $row->nameUa . ' - ' . $row->name);

            $parcelArrayInfo[] = $row->name;
            $parcelArrayInfo[] = $row->nameUa;
            $parcelArrayInfo[] = 0;

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

            $weight = '0.3';
            if ($productType == 'Алмазна мозаїка') {
                $weight = '1';
            }
            $parcelArrayInfo[] = $productType;
            if ($row->price > $row->recommendedPrice) {
                $row->recommendedPrice = $row->price;
            }
            $parcelArrayInfo[] = $row->recommendedPrice;
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

                $translatedText = $this->translate($targetLanguage, $row->keywordsUa);
                $row->keywords = str_replace("&quot;", '"', $translatedText);
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

        return $excelData;
    }

//    public function getRoyalData($data): array{
//        $excelData = [];
//
//        return $excelData;
//    }

    private function translate($toLang, $text)
    {
        $apiKey = 'AIzaSyCqrmsw4xk4KA1qa8cHtFYU7ShKAGbpBGE';
        $response = $this->guzzle->post('https://translation.googleapis.com/language/translate/v2', [
            'query' => [
                'key' => $apiKey,
            ],
            'json' => [
                'q' => $text,
                'target' => $toLang,
            ],
        ]);

        $result = json_decode($response->getBody(), true);

        return $result['data']['translations'][0]['translatedText'];
    }

    /**
     * @throws GuzzleException
     */
    public function getRemoteData(string $url, $provider): array
    {
        $data = '';
        if ($provider == 'origami') {
            $data = json_decode(
                $this->guzzle->get(
                    $url, []
                )->getBody()->getContents(),
                true
            );
        } elseif ($provider == 'royal') {
            $time = time();
            $fileURL = 'https://royaltoys.com.ua/mprices/download/108/';
            $filePath = storage_path('public');
            echo $filePath;exit();
            $fileContent = file_get_contents($fileURL);
            file_put_contents($filePath, $fileContent);

//            $spreadsheet = IOFactory::load($localFilePath);
//            $worksheet = $spreadsheet->getActiveSheet();
//            $highestRow = $worksheet->getHighestRow();
//            $highestColumn = $worksheet->getHighestColumn();
//
//            $dataCell = [];
//            for ($row = 1; $row <= $highestRow; ++$row) {
//                $newRow = [];
//                $i = 0;
//                for ($col = 'A'; $col <= $highestColumn; ++$col) {
//                    $cellValue = $worksheet->getCell($col . $row)->getValue();
//                    echo $cellValue . ' ';
//                    $newRow[$i] = $cellValue;
//                    $i++;
//                }
//                $dataCell[] = $newRow;
//                echo PHP_EOL;
//            }

            //dd($tempFile);
            //$response = response()->download($tempFile, $filename);
            //dd($dataCell);
        } else {
            return 'need correct provider';
        }

        $dataResult = [];
        foreach ($data as $item) {
            $vendor = '';
            $nameUa = '';
            $imageUrl = '';
            $vendorCode = '';
            $productType = '';
            $size = '';
            $price = '';
            $recommendedPrice = '';
            $quantityInStock = 0;
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
            }
            if ($provider == 'royal') {
                $vendorCode = '';
                $imageUrl = '';
                $nameUa = '';
                $productType = '';
                $size = '';
                $price = '';
                $recommendedPrice = '';
                $quantityInStock = '';
            }

            $dataResult[] = [
                'vendorCode' => trim($vendorCode),
                'vendor' => trim($vendor),
                'imageUrl' => $imageUrl,
                'nameUa' => $nameUa,
                'name' => '',
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
                'provider' => $provider
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
