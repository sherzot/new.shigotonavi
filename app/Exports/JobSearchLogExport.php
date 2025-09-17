<?php

namespace App\Exports;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class JobSearchLogExport implements FromCollection
{
    public function collection()
    {
        $logPath = storage_path('logs/job_search.log');

        if (!File::exists($logPath)) {
            return collect([
                ['ファイルが存在しません']
            ]);
        }

        $lines = File::lines($logPath)->toArray();

        // 🔍 すべての職種コードを読み込む (big + middle を key として結合)
        $jobTypeMaster = DB::table('master_job_type')
            ->select('big_class_code', 'middle_class_code', 'big_class_name', 'middle_clas_name')
            ->get()
            ->mapWithKeys(function ($item) {
                $key = $item->big_class_code . $item->middle_class_code;
                return [$key => [
                    'big_name' => $item->big_class_name,
                    'middle_name' => $item->middle_clas_name,
                ]];
            });

        $header = [
            'ログタイプ', '日時',
            '職種コード（名）', '職種タイプ（名）', '希望年収(万円)', '希望時給',
            '勤務地', '資格', '補足フラグ数',
            'IPアドレス', '端末情報',
            '検索結果件数', '検索済みか', '表示時間',
        ];

        $dataRows = collect($lines)->map(function ($line) use ($jobTypeMaster) {
            preg_match('/^\[(.*?)\]\s+\w+:\s+(🔍|📊)(.*?)$/u', $line, $matches);
            if (!$matches) return null;

            $datetime = $matches[1] ?? '';
            $logType = trim($matches[2] ?? '');
            $jsonRaw = trim($matches[3] ?? '');
            $decoded = json_decode($jsonRaw, true);

            if (!is_array($decoded)) return null;

            if ($logType === '🔍') {
                $bigCode = $decoded['職種コード'] ?? '';
                $middleCode = $decoded['職種タイプ'] ?? '';
                $key = $bigCode . $middleCode;

                $bigName = $jobTypeMaster[$key]['big_name'] ?? $bigCode;
                $middleName = $jobTypeMaster[$key]['middle_name'] ?? $middleCode;

                return [
                    $logType,
                    $datetime,
                    $bigName,
                    $middleName,
                    $decoded['希望年収(万円)'] ?? '',
                    $decoded['希望時給'] ?? '',
                    $decoded['勤務地'] ?? '',
                    $decoded['資格'] ?? '',
                    is_array($decoded['補足フラグ'] ?? null) ? count($decoded['補足フラグ']) : 0,
                    $decoded['IPアドレス'] ?? '',
                    $decoded['端末情報'] ?? '',
                    '', '', ''
                ];
            }

            if ($logType === '📊') {
                return [
                    $logType,
                    $datetime,
                    '', '', '', '', '', '', '',
                    '', '',
                    $decoded['表示件数'] ?? '',
                    $decoded['検索済みか'] ?? '',
                    $decoded['表示時刻'] ?? ''
                ];
            }

            return null;
        })->filter();

        return collect([$header])->concat($dataRows);
    }
}
