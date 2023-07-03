<?php

namespace App\Console\Commands;

use App\Models\Settings;
use Exception;
use Illuminate\Console\Command;

class UpdateNbuCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:currency';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update currency exchange';

    /**
     * Create a new command instance.
     *
     */
    public function __construct()
    {
        parent::__construct();

    }

    public function execCurl($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }

    /**
     * @throws Exception
     */
    public function handle(): void
    {

        $settingsUsd = Settings::query()->where('key_name', '=', 'nbu_usd')->first();
        $settingsEur = Settings::query()->where('key_name', '=', 'nbu_eur')->first();

        $usdUrl = 'https://bank.gov.ua/NBUStatService/v1/statdirectory/exchange?valcode=USD&date=' . date('Y') . date(
                'm'
            ) . date('d') . '&json';
        $eurUrl = 'https://bank.gov.ua/NBUStatService/v1/statdirectory/exchange?valcode=EUR&date=' . date('Y') . date(
                'm'
            ) . date('d') . '&json';

        $usd = json_decode($this->execCurl($usdUrl));
        $eur = json_decode($this->execCurl($eurUrl));

        dump($usd);
        dump($eur);
        dump($eur[0]->rate);
        dump($usd[0]->rate);

        if ($eur) {
            $settingsEur->value = $eur[0]->rate;
            $settingsEur->save();
            $settingsUsd->value = $usd[0]->rate;
            $settingsUsd->save();
        }
    }
}
