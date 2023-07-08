<?php

namespace App\Console\Commands;

use App\Models\OrigamiProducts;
use App\Services\DropService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class UpdatePromFileCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:export {provider}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update export xls file';

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

    /**
     * @throws GuzzleException
     */
    public function handle(): void
    {
        $inputKey = $this->argument('provider');
        OrigamiProducts::query()->update(['active' => 0]);

        $data = [];
        $url = '';
        if ($inputKey == 'origami') {
            $url = 'https://origami.bycof.com/drop-api/products';
        }

        if ($inputKey == 'royal') {
            $url = 'https://royaltoys.com.ua/mprices/download/108/';
        }

        $data = $this->dropService->getRemoteData($url, $inputKey);
        $template = 'export-origami.xls';

        foreach ($data as $apiProduct) {
            $vendor = $apiProduct['vendor'];
            $oneProduct = OrigamiProducts::query()
                ->where(
                    'vendorCode',
                    trim($apiProduct['vendorCode'])
                )
                ->where('provider', $inputKey)
                ->first();

            if ($oneProduct) {
                if ($apiProduct['vendor'] == "Рів'єра") {
                    $vendor = 'Riviera';
                } elseif ($apiProduct['vendor'] == "Орігамі") {
                    $vendor = 'Origami';
                }

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
                $oneProduct->nameUa = str_replace("&quot;", '"', $apiProduct['nameUa']);
                $oneProduct->nameUa = str_replace("  ", ' ', $oneProduct->nameUa);

                $oneProduct->name = str_replace("&quot;", '"', $apiProduct['name']);
                $oneProduct->name = str_replace("  ", ' ', $oneProduct->name);

                $oneProduct->vendorCode = trim($apiProduct['vendorCode']);
                $oneProduct->productUrl = $apiProduct['productUrl'];
                if ($oneProduct->nameUa != '' && $oneProduct->name != '') {
                    $oneProduct->active = 1;
                }
                $oneProduct->save();
            } else {
                $promId = 0;
                if (isset($apiProduct['promID'])) {
                    $promId = $apiProduct['promID'];
                }
                OrigamiProducts::query()
                    ->create(
                        [
                            'vendorCode' => trim($apiProduct['vendorCode']),
                            'vendor' => trim($vendor),
                            'imageUrl' => $apiProduct['imageUrl'],
                            'nameUa' => str_replace(PHP_EOL, '', $apiProduct['nameUa']),
                            'name' => str_replace(PHP_EOL, '', $apiProduct['name']),
                            'promID' => $promId,
                            'description' => '',
                            'description_ua' => '',
                            'productType' => $apiProduct['productType'],
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

        $excelData = $this->dropService->getExcelData($data);

        $spreadsheet = IOFactory::load(resource_path() . '/templates/' . $template);
        $spreadsheet->getActiveSheet()->fromArray(
            $excelData,
            null,
            'A2'
        );

        $writer = IOFactory::createWriter($spreadsheet, 'Xls');
        $path = '/example/' . $template;
        $file = public_path() . $path;
        $writer->save($file);
    }

    private function translateAi()
    {
    }

}
