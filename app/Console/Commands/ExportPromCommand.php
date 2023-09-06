<?php

namespace App\Console\Commands;

use App\Services\DropService;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Exception;

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
        $template = 'export-origami.xlsx';
        $this->extracted($template);
    }

    /**
     * @param string $template
     * @throws Exception
     */
    public function extracted(string $template): void
    {
        $excelData = $this->dropService->getExcelData();

        dd($excelData);
        $spreadsheet = IOFactory::load(resource_path() . '/templates/' . $template);
        $spreadsheet->getActiveSheet()->fromArray(
            $excelData,
            null,
            'A2'
        );

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $file = public_path() . '/example/' . $template;
        $writer->save($file);
    }
}
