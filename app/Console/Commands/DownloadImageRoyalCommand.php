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
    protected $description = 'Download images';

    private function downloadImageFiles()
    {
        $dir = storage_path("app/public/royal/images/");
        $products = Product::query()
            ->where('photo', null)
            ->orwhere('photo', '')
            ->get();
        foreach ($products as $product) {
            if ($product->imageUrl) {
                $to = [];
                foreach ($product->imageUrl as $image) {
                    $f = $product->id;
                    $localFilePath = $dir . $f . '.jpg';
                    $imageFile = file_get_contents($image);
                    if ($imageFile === false) {
                        die('Error: Unable to fetch the XML content from the URL.');
                    }
                    if (!file_exists($localFilePath)) {
                        $result = file_put_contents($localFilePath, $imageFile);
                        if ($result === false) {
                            die('Error: Unable to save the XML content to the local file.');
                        }
                        $s = '/storage/royal/images/' . $f . '.jpg';
                        $to[] = $s;
                        echo 'Image - .' . $s . PHP_EOL;
                    }
                }

                $product->photo = implode(',', $to);
                $product->save();
            }
        }
    }

    #[NoReturn] public function handle(): void
    {
        $this->downloadImageFiles();


//        $handle = curl_init('https://en3jud4gtbvqi.x.pipedream.net/');
//        $data = [
//            'url' => $xmlRuUrl
//        ];
//        $encodedData = json_encode($data);
//        curl_setopt($handle, CURLOPT_POST, 1);
//        curl_setopt($handle, CURLOPT_POSTFIELDS, $encodedData);
//        curl_setopt($handle, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
//        $result = curl_exec($handle);
    }
}
