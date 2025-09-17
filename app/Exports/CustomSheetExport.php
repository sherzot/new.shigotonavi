<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeSheet;

class CustomSheetExport implements FromCollection, WithEvents, WithTitle
{
    public function collection()
    {
        // DBからデータ取得
        return DB::table('master_person')
            ->select('staff_code', 'name', 'mail_address')
            ->where('staff_code', 'S1412117')
            ->get();
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function(BeforeSheet $event) {
                // テンプレートをロード
                $templatePath = storage_path('app/templates/template.xlsx');
                $spreadsheet = IOFactory::load($templatePath);
                $templateSheet = $spreadsheet->getActiveSheet(); // テンプレートのシートを取得

                // `$event->sheet->getDelegate()` にテンプレートの内容をコピー
                $event->sheet->getDelegate()->setTitle('ユーザーリスト');

                // `collection()` から取得したデータを挿入
                $users = $this->collection();
                $row = 2; // 2行目からデータを書き込む

                foreach ($users as $user) {
                    $event->sheet->setCellValue("A{$row}", $user->staff_code);
                    $event->sheet->setCellValue("B{$row}", $user->name);
                    $event->sheet->setCellValue("C{$row}", $user->mail_address);
                    $row++;
                }
            }
        ];
    }

    public function title(): string
    {
        return 'ユーザーリスト';
    }
}

