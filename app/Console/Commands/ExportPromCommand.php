<?php

namespace App\Console\Commands;

use App\Services\DropService;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExportPromCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:export_prom';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export xls file';

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

    public function handle(): void
    {
        $template = 'export-origami.xls';
        $excelData = $this->dropService->getExcelData();

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

    public function preparedData(){
        $excelData = $this->dropService->getExcelData();

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
