<?php

namespace App\Console\Commands;

use App\Models\OrigamiProducts;
use App\Services\DropService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;

class UpdatePromFileCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected string $signature = 'update:export {inputKey}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update export xls file';

    private $dropService;

    /**
     * Create a new command instance.
     *
     */
    public function __construct(DropService $dropService)
    {
        $this->dropService = $dropService;
        parent::__construct();
    }

    /**
     * @throws GuzzleException
     */
    public function handle(): void
    {
        $inputKey = $this->argument('inputKey');
        OrigamiProducts::query()->update(['active' => 0]);

        $data = [];
        $dropProvider = 1;
        $url = '';
        if ($inputKey == 'origami') {
            $url = 'https://origami.bycof.com/drop-api/products';
        }

        if ($inputKey == 'royal') {
            $url = 'https://royaltoys.com.ua/mprices/download/108/';
            $dropProvider = 2;
        }

        $data = $this->dropService->getRemoteData($url, $inputKey);
        $template = 'export-origami.xls';

        dd($data);

//        foreach ($data as $apiProduct) {
//            $vendor = $apiProduct['vendor'];
//            $oneProduct = OrigamiProducts::query()
//                ->where(
//                    'vendorCode',
//                    trim($apiProduct['vendorCode'])
//                )
//                ->where('provider', $dropProvider)
//                ->first();
//
//            if ($oneProduct) {
//                if ($apiProduct['vendor'] == "Рів'єра") {
//                    $vendor = 'Riviera';
//                } elseif ($apiProduct['vendor'] == "Орігамі") {
//                    $vendor = 'Origami';
//                }
//
//                if ($apiProduct['vendorCode'] == 'AL001') {
//                    $productType = 'Акриловий лак';
//                } else {
//                    if ($apiProduct['productType'] == 'Інше') {
//                        $productType = 'Розмальовка  для дітей';
//                    } else {
//                        $productType = $apiProduct['productType'];
//                    }
//                }
//                $recommendedPrice = $apiProduct['recommendedPrice'];
//                if ($apiProduct['price'] > $apiProduct['recommendedPrice']) {
//                    $recommendedPrice = $apiProduct['price'];
//                }
//                $oneProduct->price = $apiProduct['price'];
//                $oneProduct->recommendedPrice = $recommendedPrice;
//                $oneProduct->vendor = $vendor;
//                $oneProduct->productType = $productType;
//                $oneProduct->nameUa = str_replace("&quot;", '"', $apiProduct['nameUa']);
//                $oneProduct->nameUa = str_replace("  ", ' ', $oneProduct->nameUa);
//
//                $oneProduct->vendorCode = trim($apiProduct['vendorCode']);
//                if ($oneProduct->nameUa != '' && $oneProduct->name != '') {
//                    $oneProduct->active = 1;
//                }
//                $oneProduct->save();
//            } else {
//                OrigamiProducts::query()
//                    ->create(
//                        [
//                            'vendorCode' => trim($apiProduct['vendorCode']),
//                            'vendor' => trim($vendor),
//                            'imageUrl' => $apiProduct['imageUrl'],
//                            'nameUa' => str_replace(PHP_EOL, '', $apiProduct['nameUa']),
//                            'name' => '',
//                            'promID' => $apiProduct['promID'],
//                            'description' => '',
//                            'description_ua' => '',
//                            'productType' => $apiProduct['productType'],
//                            'size' => $apiProduct['size'],
//                            'price' => $apiProduct['price'],
//                            'recommendedPrice' => $apiProduct['recommendedPrice'],
//                            'quantityInStock' => $apiProduct['quantityInStock'],
//                            'hasHigherPrice' => $apiProduct['hasHigherPrice'],
//                            'active' => 0,
//                            'provider' => $dropProvider,
//                        ]
//                    );
//            }
//        }
//
//        $excelData = $this->dropService->getExcelData($data);
//
//        $spreadsheet = IOFactory::load(resource_path() . '/export_template/' . $template);
//        $spreadsheet->getActiveSheet()->fromArray(
//            $excelData,
//            null,
//            'A2'
//        );
//
//        $writer = IOFactory::createWriter($spreadsheet, 'Xls');
//        $path = '/example/' . $template;
//        $file = public_path() . $path;
//        $writer->save($file);
    }

    private function translateAi()
    {
    }

}
