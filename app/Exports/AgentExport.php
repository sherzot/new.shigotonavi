<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment as Align;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup as PageSetup;
use App\Models\PersonPicture;
use Illuminate\Support\Facades\Storage;


class AgentExport implements WithEvents
{

        use Exportable;

        protected $selectDate;

        public function __construct($selectDate)
        {
                $this->selectDate = $selectDate;
        }
        public function registerEvents(): array
        {
                return [
                        AfterSheet::class => function (AfterSheet $event) {
                                $this->makeDailySheet($this->selectDate, $event);
                        },
                ];
        }
        public function makeDailySheet($selectDate, $event)
        {
                //dd('function makeDailySheet Called');
                DB::enableQueryLog();
                // Excel出力
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet(); //

                $tNextDay = strtotime($selectDate . ' +1 day');
                $nextDate = date("Y-m-d H:i:s", $tNextDay);
                $firstDate = $selectDate . ' 00:00:00';

                //dd($selectDate . '  '  . $nextDate);
                $users = DB::table('master_person as mp')
                        ->leftJoin('person_offer as po', 'mp.staff_code', '=', 'po.staff_code')
                        ->leftJoin('person_career_history as pc', 'mp.staff_code', '=', 'pc.staff_code')
                        ->leftJoin('log_person_signin as lps', 'mp.staff_code', '=', 'lps.staff_code')
                        ->leftJoin('person_hope_job_type as phj', 'mp.staff_code', '=', 'phj.staff_code')
                        ->leftJoin('master_code as mc', function ($join) {
                                $join->on('mc.code', '=', 'mp.prefecture_code')
                                        ->where('mc.category_code', '=', 'Prefecture');
                        })
                        ->leftJoin('person_hope_working_condition as phw', 'mp.staff_code', '=', 'phw.staff_code')
                        ->leftJoin('master_job_type as mjt', function ($join) {
                                $join->on(DB::raw("CONCAT(lps.search_big_class_code, lps.search_job_category, '000')"), '=', 'mjt.all_connect_code');
                        })
                        ->select(
                                'mp.created_at',
                                'mp.staff_code',
                                'mp.name',
                                'mp.birthday',
                                // 'mp.age',
                                'mp.sex',
                                'mc.detail as prefecture',
                                'mp.city',
                                'mp.portable_telephone_number as tel',
                                'mp.mail_address',
                                'pc.company_name',
                                'pc.job_type_detail as career_job',
                                'phj.job_type_detail as hope_job',
                                'po.order_code as offer',
                                'lps.register_action',
                                'lps.mypage_at',
                                'lps.match_count',
                                'lps.update_count',
                                'lps.detail_count',
                                'lps.search_big_class_code',
                                'lps.search_job_category',
                                'mjt.big_class_name as search_big_class_name',
                                'mjt.middle_clas_name as search_middle_class_name',
                                'lps.filter_salary',
                                'lps.filter_hourly_wage',
                                'lps.filter_location',
                                'lps.filter_flags',
                                'lps.last_viewed_job',
                                'phw.yearly_income_min',
                                'phw.hourly_income_min',
                        )
                        // ->where('mp.created_at', '>=', $firstDate)
                        // ->where('mp.created_at', '<', $nextDate)
                        ->where(function ($query) use ($firstDate, $nextDate) {
                                $query->whereBetween('mp.created_at', [$firstDate, $nextDate])
                                        ->orWhere(function ($q) use ($firstDate, $nextDate) {
                                                $q->whereNotNull('po.order_code')
                                                        ->where('po.update_at', '>=', $firstDate)
                                                        ->where('po.update_at', '<', $nextDate);
                                        });
                        })

                        // ->where(function ($query) use ($firstDate, $nextDate) {
                        //         $query->whereBetween('mp.created_at', [$firstDate, $nextDate])
                        //                 ->orWhereNotNull('po.order_code');
                        // })
                        // ->where('po.update_at', '>=', $firstDate)
                        // ->where('po.update_at', '<', $nextDate)
                        ->where('mp.name', 'NOT LIKE', 'SHER%')
                        ->where('mp.name', 'NOT LIKE', '%テスト%')
                        //->limit(5)
                        ->groupBy('mp.staff_code')
                        ->orderby('mp.created_at')
                        ->orderby('pc.id', 'desc')
                        ->get();

                $oldUsers = DB::table('master_person as mp')
                        ->leftJoin('person_offer as po', 'mp.staff_code', '=', 'po.staff_code')
                        ->leftJoin('person_career_history as pc', 'mp.staff_code', '=', 'pc.staff_code')
                        ->leftJoin('log_person_signin as lps', 'mp.staff_code', '=', 'lps.staff_code')
                        ->leftJoin('person_hope_job_type as phj', 'mp.staff_code', '=', 'phj.staff_code')
                        ->leftJoin('master_code as mc', function ($join) {
                                $join->on('mc.code', '=', 'mp.prefecture_code')
                                        ->where('mc.category_code', '=', 'Prefecture');
                        })
                        ->leftJoin('person_hope_working_condition as phw', 'mp.staff_code', '=', 'phw.staff_code')
                        ->leftJoin('master_job_type as mjt', function ($join) {
                                $join->on(DB::raw("CONCAT(lps.search_big_class_code, lps.search_job_category, '000')"), '=', 'mjt.all_connect_code');
                        })
                        ->select(
                                'mp.created_at',
                                'mp.staff_code',
                                'mp.name',
                                'mp.birthday',
                                // 'mp.age',
                                'mp.sex',
                                'mc.detail as prefecture',
                                'mp.city',
                                'mp.portable_telephone_number as tel',
                                'pc.company_name',
                                'pc.job_type_detail as career_job',
                                'mp.mail_address',
                                'phj.job_type_detail as hope_job',
                                'po.order_code as offer',
                                'lps.register_action',
                                'lps.mypage_at',
                                'lps.match_count',
                                'lps.update_count',
                                'lps.detail_count',
                                'lps.search_big_class_code',
                                'lps.search_job_category',
                                'mjt.big_class_name as search_big_class_name',
                                'mjt.middle_clas_name as search_middle_class_name',
                                'lps.filter_salary',
                                'lps.filter_hourly_wage',
                                'lps.filter_location',
                                'lps.filter_flags',
                                'lps.last_viewed_job',
                                'phw.yearly_income_min',
                                'phw.hourly_income_min'
                        )
                        ->where(function ($query) use ($firstDate, $nextDate) {
                                $query->whereBetween('lps.update_at', [$firstDate, $nextDate])
                                        ->orWhere(function ($q) use ($firstDate, $nextDate) {
                                                $q->whereNotNull('po.order_code')
                                                        ->where('po.update_at', '>=', $firstDate)
                                                        ->where('po.update_at', '<', $nextDate);
                                        });
                        })

                        // ->where('lps.update_at', '>=', $firstDate)
                        // ->where('lps.update_at', '<', $nextDate)
                        // ->where('mp.staff_code', 'LIKE', 'S1%')
                        ->where('mp.name', 'NOT LIKE', 'SHER%')
                        ->where('mp.name', 'NOT LIKE', '片山%')
                        //->limit(5)
                        ->groupBy('mp.staff_code')
                        ->orderby('lps.update_at')
                        ->orderby('pc.id', 'desc')
                        ->get();
                //dd(DB::getQueryLog());
                $existingCodes = $users->pluck('staff_code')->toArray(); // 🟢 新規ユーザーのコードを収集
                $cnt = 0;
                if ($users) {
                        $sheet->setCellValue("B1", "登録求職者一覧 " . $selectDate);
                        $sheet->getStyle("B1")->getFont()->setSize('16');
                        $sheet->setCellValue("A3", "No");
                        $sheet->setCellValue("B3", "登録日時");
                        $sheet->setCellValue("C3", "Staff_code");
                        $sheet->setCellValue("D3", "氏名");
                        $sheet->setCellValue("E3", "誕生日");
                        $sheet->setCellValue("F3", "年齢");
                        $sheet->setCellValue("G3", "性別");
                        $sheet->setCellValue("H3", "都道府県");
                        $sheet->setCellValue("I3", "市町村");
                        $sheet->setCellValue("J3", "電話");
                        $sheet->setCellValue("K3", "Mail");
                        $sheet->setCellValue("L3", "直近勤務先");
                        $sheet->getStyle("L3")->getAlignment()->setWrapText(true);
                        $sheet->setCellValue("M3", "経験職種");
                        $sheet->setCellValue("N3", "希望職種");
                        $sheet->setCellValue("O3", "希望勤務形態");
                        $sheet->setCellValue("P3", "Mypage_access");
                        $sheet->getStyle("P3")->getAlignment()->setWrapText(true);
                        $sheet->setCellValue("Q3", "求人一覧表示数");
                        $sheet->getStyle("Q3")->getAlignment()->setWrapText(true);
                        $sheet->setCellValue("R3", "絞込数");
                        $sheet->setCellValue("S3", "詳細閲覧数");
                        $sheet->getStyle("S3")->getAlignment()->setWrapText(true);
                        $sheet->setCellValue("T3", "オファー");                        
                        $sheet->getColumnDimension('A')->setWidth(6);
                        $sheet->getColumnDimension('B')->setWidth(18);
                        $sheet->getColumnDimension('C')->setWidth(10);
                        $sheet->getColumnDimension('D')->setWidth(18);
                        $sheet->getColumnDimension('E')->setWidth(10);
                        $sheet->getColumnDimension('F')->setWidth(5);
                        $sheet->getColumnDimension('G')->setWidth(5);
                        $sheet->getColumnDimension('H')->setWidth(8);
                        $sheet->getColumnDimension('I')->setWidth(10);
                        $sheet->getColumnDimension('J')->setWidth(16);
                        $sheet->getColumnDimension('K')->setWidth(28);
                        $sheet->getColumnDimension('L')->setWidth(16);
                        $sheet->getColumnDimension('M')->setWidth(16);
                        $sheet->getColumnDimension('N')->setWidth(22);
                        $sheet->getColumnDimension('O')->setWidth(10);
                        $sheet->getColumnDimension('P')->setWidth(20);
                        $sheet->getColumnDimension('Q')->setWidth(8);
                        $sheet->getColumnDimension('R')->setWidth(8);
                        $sheet->getColumnDimension('S')->setWidth(8);
                        $sheet->getColumnDimension('T')->setWidth(8);
                } //if ($users)
                $row = 4;
                $cnt = 1;
                $tempCode = "";
                $exportedStaffCodes = []; // ✅ 以前のユーザーコードを収集する準備ができています。
                foreach ($users as $user) {
                        if ($user->staff_code == $tempCode) continue;

                        $birthDate = Carbon::parse($user->birthday)->format("Y-m-d");

                        $workingStyle = '';
                        if ($user->yearly_income_min > 0) {
                                $workingStyle = "正社員";
                        } elseif ($user->hourly_income_min > 0) {
                                $workingStyle = "派遣";
                        }

                        $sheet->setCellValue("A{$row}", $cnt);
                        $sheet->setCellValue("B{$row}", $user->created_at);
                        $sheet->setCellValue("C{$row}", $user->staff_code);
                        $sheet->setCellValue("D{$row}", $user->name);
                        $sheet->setCellValue("E{$row}", $birthDate);
                        $sheet->setCellValue("F{$row}", Carbon::parse($user->birthday)->age);
                        $sheet->setCellValue("G{$row}", $user->sex);
                        $sheet->setCellValue("H{$row}", $user->prefecture);
                        $sheet->setCellValue("I{$row}", $user->city);
                        $sheet->setCellValue("J{$row}", $user->tel);
                        $sheet->getStyle("J{$row}")->getAlignment()->setWrapText(true);
                        $sheet->setCellValue("K{$row}", $user->mail_address ?? '');
                        $sheet->setCellValue("L{$row}", $user->company_name ?? '');
                        $sheet->setCellValue("M{$row}", $user->career_job?? '');
                        $sheet->setCellValue("N{$row}", $user->hope_job ?? '');
                        $sheet->getStyle("N{$row}")->getAlignment()->setWrapText(true);
                        $sheet->setCellValue("O{$row}", $workingStyle ?? '');
                        $sheet->getStyle("O{$row}")->getAlignment()->setWrapText(true);
                        $sheet->setCellValue("P{$row}", $user->mypage_at ?? '');
                        $sheet->setCellValue("Q{$row}", $user->match_count ?? '');
                        $sheet->setCellValue("R{$row}", $user->update_count ?? '');
                        $sheet->setCellValue("S{$row}", $user->detail_count ?? '');
                        $sheet->setCellValue("T{$row}", $user->offer ?? '');
                        // $sheet->setCellValue("U{$row}", $user->offer ?? '');
                        ++$row;
                        ++$cnt;
                        $tempCode = $user->staff_code;
                }

                $lastRow = $row - 1;
                // $sheet->setCellValue("B{$row}", "旧しごとナビ登録者マッチング等");
                $sheet->setCellValue("B{$row}", "旧しごとナビ登録者マッチング等");

                // 🟨 この行からA～U列まで黄色の背景
                $sheet->getStyle("A{$row}:T{$row}")
                ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('FFFF00');

                ++$row;
                foreach ($oldUsers as $old) {
                        // if (in_array($old->staff_code, $exportedStaffCodes)) continue;
                        if (in_array($old->staff_code, $existingCodes)) continue; // 🔁 新規登録者と被る場合はスキップ
                        $birthDate = Carbon::parse($old->birthday)->format("Y-m-d");

                        $workingStyle = '';
                        if ($old->yearly_income_min > 0) {
                                $workingStyle = "正社員";
                        } elseif ($old->hourly_income_min > 0) {
                                $workingStyle = "派遣";
                        }

                        $sheet->setCellValue("A{$row}", $cnt);
                        $sheet->setCellValue("B{$row}", $old->created_at);
                        $sheet->setCellValue("C{$row}", $old->staff_code);
                        $sheet->setCellValue("D{$row}", $old->name);
                        $sheet->setCellValue("E{$row}", $birthDate);
                        $sheet->setCellValue("F{$row}", Carbon::parse($old->birthday)->age);
                        $sheet->setCellValue("G{$row}", $old->sex);
                        $sheet->setCellValue("H{$row}", $old->prefecture);
                        $sheet->setCellValue("I{$row}", $old->city);
                        $sheet->setCellValue("J{$row}", $old->tel);
                        $sheet->getStyle("J{$row}")->getAlignment()->setWrapText(true);
                        $sheet->setCellValue("K{$row}", $old->mail_address ?? '');
                        $sheet->setCellValue("L{$row}", $old->company_name ?? '');
                        $sheet->getStyle("L{$row}")->getAlignment()->setWrapText(true);
                        $sheet->setCellValue("M{$row}", $old->career_job ?? '');
                        $sheet->setCellValue("N{$row}", $old->hope_job ?? '');
                        $sheet->getStyle("N{$row}")->getAlignment()->setWrapText(true);
                        $sheet->setCellValue("O{$row}", $workingStyle?? '');
                        $sheet->getStyle("O{$row}")->getAlignment()->setWrapText(true);
                        $sheet->setCellValue("P{$row}", $old->mypage_at ?? '');
                        $sheet->setCellValue("Q{$row}", $old->match_count ?? '');
                        $sheet->setCellValue("R{$row}", $old->update_count ?? '');
                        $sheet->setCellValue("S{$row}", $old->detail_count ?? '');
                        $sheet->setCellValue("T{$row}", $old->offer ?? '');
                        // $sheet->setCellValue("U{$row}", $old->offer ?? '');

                        $tempCode = $old->staff_code;
                        ++$row;
                        ++$cnt;
                }
                //}// end if


                $styleArray = [
                        'borders' => [
                                'allBorders' => [
                                        'borderStyle' => Border::BORDER_THIN,
                                ],
                        ],
                ];

                $last = $row - 1;
                $saveDate = str_replace('-', '', $selectDate);
                $sheet->getStyle("A3:T{$lastRow}")->applyFromArray($styleArray);
                $firstRow = $lastRow + 2;
                $sheet->getStyle("A{$firstRow}:T{$last}")->applyFromArray($styleArray);
                //$sheet->setCellValue("A{$firstRow}:S{$last}");
                // $tBirthDay = strtotime($user->birthday);
                // $birthDate = Carbon::parse($user->birthday)->format('Y-m-d');
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 3); //1行目を先頭に3行目までが見出しとして繰り返される
                $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE); //横向き
                $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4); //A4サイズ
                //$spreadsheet->getActiveSheet()->freezePane("B2");
                // 📌 **ファイルを保存**
                $exportPath = storage_path("app/private/exports/stafflist-" . $saveDate . ".xlsx");
                // dd('exportTah=' . $exportPath);
                $writer = IOFactory::createWriter($spreadsheet, "Xlsx");
                $writer->save($exportPath);
        } // end function
}// End ofClass
