<?php

namespace App\Console\Commands;

use App\Models\OrigamiProducts;
use App\Models\Product;
use App\Services\DropService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use JetBrains\PhpStorm\NoReturn;

class DownloadImageRoyalCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:download_image_royal';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download image';

    private function downloadFile($xmlUrl, $locale)
    {
        $dir = storage_path("app/public/royal/images/");
        $products = Product::query()
            ->where('photo', null)
            ->orwhere('photo', '')
            ->get();
        foreach ($products as $product) {
            $localFilePath = $dir . $product->id . '.jpg';
            if ($product->imageUrl) {
//                foreach ($product->imageUrl as $image) {
                $imageFile = file_get_contents($product->imageUrl[0]);
                if ($imageFile === false) {
                    die('Error: Unable to fetch the XML content from the URL.');
                }
//                $path = public_path('storage/royal/images/' . $product->id . '.jpg');
                if (!file_exists($localFilePath)) {
                    $result = file_put_contents($localFilePath, $imageFile);

                    if ($result === false) {
                        die('Error: Unable to save the XML content to the local file.');
                    }
                    $to = '/storage/royal/images/' . $product->id . '.jpg';
                    $product->photo = $to;
                    $product->save();
                    echo 'Image - .' . $to . PHP_EOL;
                }
//                }
            }
        }
    }

    #[NoReturn] public function handle(): void
    {
        $xmlRuUrl = 'http://dwn.royaltoys.com.ua/my/export/0e03a56a-b310-4b66-9549-e99c9dedecd1.xml';
        $this->downloadFile($xmlRuUrl, 'ru');

        $xmlUaUrl = 'http://dwn.royaltoys.com.ua/my/export/8bb6951a-0d4f-41aa-8108-b5e4b6d688c0.xml';
        $this->downloadFile($xmlUaUrl, 'ua');


        $handle = curl_init('https://en3jud4gtbvqi.x.pipedream.net/');
        $data = [
            'url' => $xmlRuUrl
        ];
        $encodedData = json_encode($data);
        curl_setopt($handle, CURLOPT_POST, 1);
        curl_setopt($handle, CURLOPT_POSTFIELDS, $encodedData);
        curl_setopt($handle, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $result = curl_exec($handle);
    }
}
