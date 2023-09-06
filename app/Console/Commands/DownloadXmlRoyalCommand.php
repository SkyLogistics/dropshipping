<?php

namespace App\Console\Commands;

use App\Models\OrigamiProducts;
use App\Services\DropService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use JetBrains\PhpStorm\NoReturn;

class DownloadXmlRoyalCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:download_royal';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download xml file';

    private function downloadFile($xmlUrl, $locale)
    {
        $dir = storage_path("app/public/royal/xml/");
        $localFilePath = $dir . $locale . '_' . 'xmlFile.xml';
        $xmlContent = file_get_contents($xmlUrl);
        if ($xmlContent === false) {
            die('Error: Unable to fetch the XML content from the URL.');
        }
        $result = file_put_contents($localFilePath, $xmlContent);
        if ($result === false) {
            die('Error: Unable to save the XML content to the local file.');
        }
        echo $locale.' XML file downloaded and saved successfully.' . PHP_EOL;
    }

    #[NoReturn] public function handle(): void
    {

        $handle = curl_init('https://envbggtgmbzfl.x.pipedream.net/');
        $data = [
            'key' => 'XML file downloaded and saved successfully'
        ];
        $encodedData = json_encode($data);

        curl_setopt($handle, CURLOPT_POST, 1);
        curl_setopt($handle, CURLOPT_POSTFIELDS, $encodedData);
        curl_setopt($handle, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_exec($handle);

        $xmlRuUrl = 'http://dwn.royaltoys.com.ua/my/export/0e03a56a-b310-4b66-9549-e99c9dedecd1.xml';
        $this->downloadFile($xmlRuUrl, 'ru');

        $xmlUaUrl = 'http://dwn.royaltoys.com.ua/my/export/8bb6951a-0d4f-41aa-8108-b5e4b6d688c0.xml';
        $this->downloadFile($xmlUaUrl, 'ua');
    }
}
