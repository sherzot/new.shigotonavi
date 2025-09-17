<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
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
use PhpOffice\PhpSpreadsheet\Style\Border;
use App\Models\PersonPicture;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\PageMargins;

class CustomExport implements WithEvents
{
	use Exportable;
	private function insertStaffPicture($sheet, $staffCode, $row): ?string
	{
	    $picture = DB::table('person_picture')
	        ->select('picture')
	        ->where('staff_code', $staffCode)
	        ->first();

	    if (!$picture || !$picture->picture) {
	        Log::warning("❌ No picture for: {$staffCode}");
	        return null;
	    }

	    $imageRaw = $picture->picture;

	    $image = str_starts_with($imageRaw, 'data:image') ?
	        base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageRaw)) :
	        $imageRaw;

	    $imageResource = @imagecreatefromstring($image);
	    if (!$imageResource) {
	        Log::error("❌ imagecreatefromstring failed for {$staffCode}");
	        Storage::disk('local')->put("debug_images/{$staffCode}.bin", $image);
	        return null;
	    }

	    $tempPath = storage_path("app/temp/photo_{$staffCode}.png");
	    if (!file_exists(dirname($tempPath))) {
	        mkdir(dirname($tempPath), 0777, true);
	    }
	    imagepng($imageResource, $tempPath);

	    $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
	    $drawing->setName('Staff Picture');
	    $drawing->setDescription('Staff Picture');
	    $drawing->setPath($tempPath); // ❗ first set path
	    $drawing->setCoordinates("N{$row}");
	    $drawing->setHeight(126);
	    $drawing->setWorksheet($sheet); // ❗ last step

	    return $tempPath; // for later deletion
	}


	public function registerEvents(): array
	{
		return [
			BeforeExport::class => function (BeforeExport $event) {
				// session staff_code
				$staffCode = Auth::user()->staff_code; //'S1080369';// 'S1412117':
				// 📌 **テンプレートを読み込む*
				//$templatePath = storage_path('app/templates/resume_jis.xlsx');
				$templatePath = storage_path('app/templates/resume_general.xlsx');
				//$templatePath = storage_path('app/templates/template.xlsx');
				$spreadsheet = IOFactory::load($templatePath);
				$sheet = $spreadsheet->getActiveSheet(); // テンプレートのシートを取得
				 $tempPath = $this->insertStaffPicture($sheet, $staffCode, 7);
				// 📌 **DBからデータを取得**
				$users = DB::table('master_person')
					->select(
						'staff_code',
						'name',
						'mail_address',
						'name_f',
						'home_telephone_number',
						'portable_telephone_number',
						'birthday',
						'city',
						'town',
						'address',
						'sex',
						'post_u',
						'post_l',
						'marriage_flag',
						'dependent_flag',
						'dependent_number',
						'spouse_flag'
					)
					->where('staff_code', $staffCode)
					->get();
				// dd($users);

				$schools = DB::table('person_educate_history as his')
					//->join('job_job_type', 'job_order.order_code', '=', 'job_job_type.order_code')
					->join('master_code as mcd', function ($join) {
						$join->on('mcd.code', '=', 'his.school_type_code')
							->where('mcd.category_code', '=', 'SchoolType');
					})
					->select('his.school_name', 'his.entry_day', 'his.graduate_day', 'his.speciality', 'mcd.detail')
					->where('staff_code', $staffCode)
					->orderBy('his.entry_day', 'asc')
					->get();
				//dd($schools);				

				$careers = DB::table('person_career_history')
					->select('staff_code', 'id', 'job_type_detail', 'company_name', 'job_type_detail', 'entry_day', 'retire_day')
					->where('staff_code', $staffCode)
					->orderBy('entry_day', 'asc')
					->get();
				//$image = imagecreatefromstring($picture->picture);
				//	//dd($careers);

				// `staff_code` に対応する画像を取得
				// `staff_code` に対応する画像を取得
				


				// 一時フォル/ダに画像を保存
				//$tempPath = storage_path("app/public/temp_{$staffCode}.jpg");
				//$tempPath = public_path('temp_S1423367.jpg');
				//file_put_contents($tempPath, $picture->picture);

				//dd($tempPath);
				// **Excel に画像を貼り付け**
				//$drawing = new Drawing();
				//$drawing->setPath(public_path('temp_S1423367.jpg'));
				//$drawing->setPath(storage_path("app/public/temp_{$staffCode}.jpg"));
				//$d124Grawing->setPath($tempPath); // 画像のパス
				//$drawing->setCoordinates('N8'); // 貼り付けるセル
				//$drawing->setResizeProportional(true);
				//$drawing->setWidth(100); // 画像の幅
				//$drawing->setOffsetX(1); //(5);
				//$drawing->setOffsetY(1);//(5);
				//$drawing->setWorksheet($sheet);

				// maru image insert
				if ($users[0]->sex == '1' || $users[0]->sex == '2') {
					$drawing2 = new Drawing();
					$drawing2->setName('CheckImage');
					$drawing2->setDescription('まる');
					//$drawing2->setPath(public_path('images/maru.png')); // 画像のパス
					//dd(storage_path('app/public/images/maru.png'));
					$drawing2->setPath(storage_path('app/public/images/maru2.png'));
					//$drawing2->setPath(storage_path('app/public/images/maru.png'));
					$drawing2->setResizeProportional(true); //画像をリサイズするかどうか
					$drawing2->setHeight(20); // サイズ調整（10pt に合わせるには15〜20くらいが目安）
					$drawing2->setCoordinates('M24'); // 挿入先のセル
					if ($users[0]->sex == "1") {
						$drawing2->setOffsetX(20);
					} elseif ($users[0]->sex == "2") {
						$drawing2->setOffsetX(46);
					}
					$drawing2->setOffsetY(3);
					$drawing2->setWorksheet($sheet);
				}

				//foreach ($users as $user ) {
				//$row = 117;
				//$sheet->setCellValue("M{$row}", $user->dependent_number);
				//Maru image
				$spreadsheet->setActiveSheetIndex(1);
				$sheet2 = $spreadsheet->getActiveSheet();
				$drawing3 = new Drawing();
				$drawing3->setName('CheckImage');
				$drawing3->setDescription('まる');
				//$drawing3->setPath(public_path('images/maru.png')); // 画像のパス
				$drawing3->setPath(storage_path('app/public/images/maru3.png'));
				//$drawing3->setPath(storage_path('app/public/images/maru.png'));
				$drawing3->setResizeProportional(true); //画像をリサイズするかどうか
				$drawing3->setHeight(24); // サイズ調整（10pt に合わせるには15〜20くらいが目安

				$row = 126;
				$drawing3->setCoordinates('K126'); // 挿入先のセル
				if ($users[0]->marriage_flag == "1") {
					$drawing3->setOffsetX(20);
					//$marrige ='有';
				} else {
					$drawing3->setOffsetX(46);
					//$marrige ='無';
				}
				if ($users[0]->dependent_flag == "1") {
					$drawing3->setCoordinates('O126'); // 挿入先のセル
					//$dependent = '有'; // ✅ 正しいコード
				} else {
					$drawing3->setCoordinates('M126'); // 挿入先のセル
					//$dependent = '無';
				}

				$drawing3->setOffsetY(3);
				$drawing3->setWorksheet($sheet2);
				//}

				$spreadsheet->setActiveSheetIndex(0);
				$sheet = $spreadsheet->getActiveSheet();
				// $this->insertStaffPicture($sheet, $staffCode, 7);
				//$spreadsheet->/ 📌 **2行目からデータを書き込む**
				$row = 14;
				foreach ($users as $user) {
					$paddress = $user->city . $user->town . $user->address;
					$birthyear = substr($user->birthday, 0, 4);
					$birthmonth = substr($user->birthday, 5, 2);
					$bday = substr($user->birthday, 8, 2);
					$personAge = Carbon::parse($user->birthday)->age;
					//$gender = $user->sex=='1'  ? '　〇　　' : '　　〇　';
					$gender = $user->sex == '1'  ? '男' : '　女';
					$syear = date('Y');
					$smonth = date('m');
					$sday = date('d');
					$postno = $user->post_u . '-' . $user->post_l;
					$sheet->setCellValue("F7", $syear);
					$sheet->setCellValue("J7", $smonth);
					$sheet->setCellValue("L7", $sday);
					$sheet->setCellValue("A24", $birthyear);
					$sheet->setCellValue("D24", $birthmonth);
					$sheet->setCellValue("F24", $bday);
					//$sheet->setCellValue("M24", $gender);
					$sheet->setCellValue("J24", $personAge);
					$sheet->setCellValue("B{$row}", $user->name);
					$sheet->setCellValue("B10", $user->name_f);
					$sheet->setCellValue("A36", $paddress);
					$sheet->setCellValue("C32", $postno);
					$sheet->setCellValue("N40", $user->portable_telephone_number);
					$sheet->setCellValue("N32", $user->home_telephone_number);
					$sheet->setCellValue("N56", $user->mail_address);
					$row++;
				}

				//dd($users);
				$row = 66;
				if ($schools) {
					$sheet->setCellValue("C{$row}", '学　歴');
					$objStyle = $sheet->getStyle("C{$row}");
					// 中央寄せ
					$objStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
					$row += 5;
					foreach ($schools as $school) {
						if ((strpos($school->school_name, '高校') == false) &&  (strpos($school->school_name, '高等学校') == false) &&  (strpos($school->school_name, '大学') == false)) {
							$schoolTemp = $school->school_name . $school->detail;
						} else {
							$schoolTemp = $school->school_name;
						}

						$schEntryYear = substr($school->entry_day, 0, 4);
						$schEntryMonth = substr($school->entry_day, 5, 2);
						$schGradYear = substr($school->graduate_day, 0, 4);
						$schGradMonth = substr($school->graduate_day, 5, 2);
						//$schoolName = $school->school_name . $school->speciality;
						$schoolName = $schoolTemp . $school->speciality;
						$objStyle = $sheet->getStyle("C{$row}");
						// 左寄せ
						$objStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

						$sheet->setCellValue("A{$row}", $schEntryYear);
						$sheet->setCellValue("B{$row}", $schEntryMonth);
						$sheet->setCellValue("C{$row}", $schoolName . '入学');
						$row += 5;
						$sheet->setCellValue("A{$row}", $schGradYear);
						$sheet->setCellValue("B{$row}", $schGradMonth);
						$sheet->setCellValue("C{$row}", $schoolName . '卒業');

						$row += 5;
					}
				} //end if($shools)

				if ($careers) {
					$sheet->setCellValue("C{$row}", '職　歴');
					$objStyle = $sheet->getStyle("C{$row}");
					// 中央寄せ
					$objStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
					$row += 5;

					$styleArray = [
						'borders' => [
							'allBorders' => [
								'borderStyle' => Border::BORDER_THIN,
							],
						],
					];


					foreach ($careers as $career) {
						$entryYear = substr($career->entry_day, 0, 4);
						$entryMonth = substr($career->entry_day, 5, 2);
						if ($career->retire_day == '0000-00-00 00:00:00') {
							$retireYear = '';
							$retireMonth = '';
						} else {
							$retireYear  = substr($career->retire_day, 0, 4);
							$retireMonth = substr($career->retire_day, 5, 2);
						}

						$sheet->setCellValue("A{$row}", $entryYear);
						$sheet->setCellValue("B{$row}", $entryMonth);
						$sheet->setCellValue("C{$row}", $career->company_name . '入社');
						if ($retireYear != '') {
							if ($row < 146) { //151
								$row += 5;
							} elseif ($row == 146) {
								$row += 6;
								$sheet->getStyle("A{$row}:O{$row}")->applyFromArray($styleArray);
							} else {
								$row++;
								$sheet->getStyle("A{$row}:O{$row}")->applyFromArray($styleArray);
							}
							$sheet->setCellValue("A{$row}", $retireYear);
							$sheet->setCellValue("B{$row}", $retireMonth);
							$sheet->setCellValue("C{$row}", $career->company_name . '退社');
						} else {
							$row += 5;
							$sheet->setCellValue("C{$row}", $career->company_name . '　現在に至る');
						}
						if ($row < 146) {
							$row += 5;
						} elseif ($row == 146) {
							$row += 6; //2;
							$sheet->getStyle("A{$row}:O{$row}")->applyFromArray($styleArray);
						} else {
							$row++;
							$sheet->getStyle("A{$row}:O{$row}")->applyFromArray($styleArray);
							$sheet->getStyle("A{$row}:O{$row}")->applyFromArray($styleArray);
						}
					}
				} //end if ($careers)
				// 幅は1ページに、縦は自動（0）
				$sheet->getPageSetup()->setFitToWidth(1);
				$sheet->getPageSetup()->setFitToHeight(0);

				// 余白も調整すると収まりがよくなります
				$sheet->getPageMargins()->setTop(0.5);
				$sheet->getPageMargins()->setBottom(0.5);
				$sheet->getPageMargins()->setLeft(0.3);
				$sheet->getPageMargins()->setRight(0.3);


				//次のシートに移動
				$spreadsheet->setActiveSheetIndex(1);

				//資格 //master_license
				$licenses = DB::table('person_license as pl')
					->join('master_license as ml', function ($join) {
						$join->on('pl.group_code', '=', 'ml.group_code')->on('pl.category_code', '=', 'ml.category_code')
							->on('pl.code', '=', 'ml.code');
					})
					->select(
						'pl.staff_code',
						'pl.id',
						'ml.group_name',
						'ml.category_name',
						'ml.name',
						'pl.get_day',
						'pl.created_at',
						'pl.update_at'
					)
					->where('staff_code', $staffCode)
					->get();
				//dd($licenses);

				//自己PR
				$prs = DB::table('person_self_pr')
					->select('staff_code', 'self_pr')
					->where('staff_code', $staffCode)
					->get();

				//スキル
				$skills = DB::table('person_skill')
					->join('master_code', function ($join) {
						$join->on('person_skill.category_code', '=', 'master_code.category_code')->on('person_skill.code', '=', 'master_code.code');
					})
					->select('person_skill.staff_code', 'master_code.detail', 'person_skill.start_day')
					->where('staff_code', $staffCode)
					->get();

				$sheet = $spreadsheet->getActiveSheet(); // テンプレートのシートを取得
				$row = 7;
				//dd($licenses);
				if ($licenses) {
					foreach ($licenses as $license) {
						$licenseYear = substr($license->get_day, 0, 4);
						$licenseMonth = substr($license->get_day, 5, 2);
						$licenseName = $license->group_name . $license->category_name  . $license->name;

						$sheet->setCellValue("A{$row}", $licenseYear);
						$sheet->setCellValue("B{$row}", $licenseMonth);
						$sheet->setCellValue("C{$row}", $licenseName);
						$row += 5;
					}
				}
				$row = 59;
				if ($prs) {
					foreach ($prs as $pr) {
						$prStr = str_replace(PHP_EOL, '', $pr->self_pr);
						$prArr = mb_str_split($prStr, 52); //48
						//$sheet->setCellValue("A{$row}", $pr->self_pr);
						foreach ($prArr as $prRow) {
							$sheet->setCellValue("A{$row}", $prRow);
							$row += 4;
							if ($row > 75) {
								break;
							}
						}
					}
				}
				//$personAge = Carbon::parse($user->birthday)i->age;	
				$row = 83;
				//dd($skills);
				if ($skills) {
					foreach ($skills as $key => $skill) {
						$skillYear = '';
						if ($skill->start_day != '0000-00-00 00:00:00') {
							$skillYear = Carbon::parse($skill->start_day)->age;
						} else {
							$skillYear = '';
						}
						if ($skillYear == 0 || $skillYear == '') {
							$sheet->setCellValue("A{$row}", $skill->detail);
						} else {
							$sheet->setCellValue("A{$row}", $skill->detail . ' 経験年数:' . $skillYear . '年');
						}
						$row += 4;
					}
				}

				$wishMotives = DB::table('person_resume_other')
					->select('staff_code', 'wish_motive', 'hope_column', 'commute_time')
					->where('staff_code', $staffCode)
					->first();
				$row = 107; //107;
				//dd($wishMotives);
				if ($wishMotives) {
					//foreach ($wishMotives as $motive) {
					$motiveStr = str_replace(PHP_EOL, '', $wishMotives->wish_motive);
					$motiveArr = mb_str_split($wishMotives->wish_motive, 34); //32
					//dd($motiveArr);
					foreach ($motiveArr as $motiveRow) {
						$sheet->setCellValue("A{$row}", $motiveRow);
						$row += 4; //5;
						if ($row > 147) {
							break;
						}
					}
					if ($wishMotives->commute_time > 0) {
						$commuteHour = floor($wishMotives->commute_time / 60);
						$commuteMinites = $wishMotives->commute_time % 60;
					} else {
						$commuteHour = Null;
						$commuteMinites = 0;
					}
					//dd($commuteHour. ' ' . $commuteMinites);
					$sheet->setCellValue("L108", $commuteHour);
					$sheet->setCellValue("N108", $commuteMinites);


					//$row=126;
					//dd($users);
					foreach ($users as $user) {
						$row = 117;
						$sheet->setCellValue("M{$row}", $user->dependent_number);
						//Maru image
						//$spreadsheet->setActiveSheetIndex(1);
						// $sheet2 = $spreadsheet->getActiveSheet();
						//$drawing3 = new Drawing();
						//$drawing3->setName('CheckImage');
						//$drawing3->setDescription('まる');
						//$drawing3->setPath(public_path('images/maru.png')); // 画像のパス
						//$drawing3->setPath(storage_path('app/public/images/maru2.png'));
						//$drawing3->setPath(storage_path('app/public/images/maru.png'));
						//$drawing3 -> setResizeProportional(true); //画像をリサイズするかどうか
						//$drawing3->setHeight(24); // サイズ調整（10pt に合わせるには15〜20くらいが目安

						$row = 126;
						$drawing3->setCoordinates('K126'); // 挿入先のセル 
						if ($user->marriage_flag == "1") {
							//$drawing3->setOffsetX(20);
							$marrige = '有';
						} else {
							//$drawing3->setOffsetX(46);
							$marrige = '無';
						}
						if ($user->dependent_flag == "1") {
							//	$drawing3->setCoordinates('O126'); // 挿入先のセル
							$dependent = '有'; // ✅ 正しいコード
						} else {
							//	$drawing3->setCoordinates('M126'); // 挿入先のセル
							$dependent = '無';
						}

						$sheet->setCellValue("K{$row}", $marrige);
						$sheet->setCellValue("O{$row}", '');
						$sheet->setCellValue("M{$row}", $dependent);
						//$drawing3->setOffsetY(3);
						//$drawing3->setWorksheet($sheet2);			
					}

					//dd($row);
					$row = 135; //185;
					$hopeArr = mb_str_split($wishMotives->hope_column, 52); //50
					foreach ($hopeArr as $hopeRow) {
						//dd($row . $hopeRow);
						$sheet->setCellValue("A{$row}", $hopeRow);
						$row += 4; // 5;
					}
				}
				//}


				$spreadsheet->setActiveSheetIndex(0);
				$spreadsheet->getActiveSheet()->freezePane('B2');
				//$spreadsheet -> getPageSetup() -> setScale(90);
				//$spreadsheet -> getPageSetup() -> setPaperSize(PageSetup::PAPERSIZE_A4); //A4サイズ
				// 📌 **ファイルを保存**
				//$exportPath = storage_path('app/exports/custom_export.xlsx');
				//$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
				//$writer->save($exportPath);

				// 📌 **エクスポート用にスプレッドシートを設定**
				//$event->writer->setSpreadsheet($spreadsheet);
				// 📌 **ファイルを保存**
				$exportPath = storage_path('app/exports/resume-' . $staffCode . '.xlsx'); //20250213
				//dd($exportPath);
				//$exportPath = storage_path('app/exports/custom_export.xlsx');
				$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
				$writer->save($exportPath);

				return collect([]);
			}
		];
	}


	public function title(): string
	{
		return '履歴書';
	}
	//}


	public function makePdf($file_name)
	{
		$staffCode = Auth::user()->staff_code;

	    $templatePath = storage_path('app/templates/resume_general.xlsx');
	    $spreadsheet = IOFactory::load($templatePath);
	    $sheet = $spreadsheet->getActiveSheet();

	    // 📸 Rasimni qo‘shish
	    $tempPath = $this->insertStaffPicture($sheet, $staffCode, 7);

		//dd( "MIME Type: " . $mimeType);

		//dd($image);
		// if ($image !== false) {
		// 	$drawing = new MemoryDrawing();
		// 	$drawing->setName('Staff Picture');
		// 	$drawing->setDescription('Staff Picture');
		// 	//$drawing->Image('@' . $image, 10, 10, 50, 50, 'JPG'); // Base64 画像の埋め込み
		// 	$drawing->setRenderingFunction(MemoryDrawing::RENDERING_JPEG);
		// 	$drawing->setMimeType(MemoryDrawing::MIMETYPE_DEFAULT);
		// 	//$drawing->setImageResource(imagecreatefromstring($image));//20250220
		// 	if (!$imageResource == false) {
		// 		$drawing->setImageResource($imageResource); //20250221
		// 		$drawing->setCoordinates("N{$row}");
		// 		$drawing->setHeight(126); //(100);
		// 		//$drawing->setOffsetX(5);
		// 		//$drawing->setOffsetY(5);
		// 		//$drawing->setWidth(100);
		// 		//$drawing->setHeight(100);
		// 		$drawing->setWorksheet($sheet);
		// 	}
		// }
		// 編集するシート名を指定
		//$worksheet = $spreadsheet->getSheetByName('hoge');

		// セルに指定した値挿入
		//$worksheet->setCellValue('A1', 'fugafuga');

		// Excel出力
		// Excel OutputFunction
		//$this->registerEvents(); //function call

		//makeExcel 20250207
		// 📌 **DBからデータを取得**
		$users = DB::table('master_person')
			->select(
				'staff_code',
				'name',
				'mail_address',
				'name_f',
				'home_telephone_number',
				'portable_telephone_number',
				'birthday',
				'city',
				'town',
				'address',
				'sex',
				'post_u',
				'post_l',
				'marriage_flag',
				'dependent_flag',
				'dependent_number',
				'spouse_flag'

			)
			->where('staff_code', $staffCode)
			->get();

		// maru image insert
		if ($users[0]->sex == "1" || $users[0]->sex == "2") {
			$drawing2 = new Drawing();
			$drawing2->setName('CheckImage');
			$drawing2->setDescription('まる');
			//$drawing2->setPath(public_path('images/maru.png')); // 画像のパス
			//dd(storage_path('app/public/images/maru.png'));
			$drawing2->setPath(storage_path('app/public/images/maru2.png'));
			//$drawing2->setPath(storage_path('app/public/images/maru.png'));
			$drawing2->setResizeProportional(true); //画像をリサイズするかどうか
			$drawing2->setHeight(20); // サイズ調整（10pt に合わせるには15〜20くらいが目安）
			$drawing2->setCoordinates('M24'); // 挿入先のセル
			if ($users[0]->sex == "1") {
				$drawing2->setOffsetX(26);
			} elseif ($users[0]->sex == "2") {
				$drawing2->setOffsetX(52);
			}
			$drawing2->setOffsetY(3);
			$drawing2->setWorksheet($sheet);
		}

		//Maru image
		$spreadsheet->setActiveSheetIndex(1);
		$sheet2 = $spreadsheet->getActiveSheet();
		$drawing3 = new Drawing();
		$drawing3->setName('CheckImage');
		$drawing3->setDescription('まる');
		$drawing3->setPath(storage_path('app/public/images/maru3.png'));
		$drawing3->setResizeProportional(true); //画像をリサイズするかどうか
		$drawing3->setHeight(24); // サイズ調整（10pt に合わせるには15〜20くらいが目安

		$row = 126;
		$drawing3->setCoordinates('K126'); // 挿入先のセル
		if ($users[0]->marriage_flag == "1") {
			$drawing3->setOffsetX(20);
		} else {
			$drawing3->setOffsetX(46);
		}
		if ($users[0]->dependent_flag == "1") {
			$drawing3->setCoordinates('O126'); // 挿入先のセル
		} else {
			$drawing3->setCoordinates('M126'); // 挿入先のセル
		}

		$drawing3->setOffsetY(3);
		$drawing3->setWorksheet($sheet2);

		$spreadsheet->setActiveSheetIndex(0);
		$sheet = $spreadsheet->getActiveSheet();


		$schools = DB::table('person_educate_history as his')
			//->join('job_job_type', 'job_order.order_code', '=', 'job_job_type.order_code')
			->join('master_code as mcd', function ($join) {
				$join->on('mcd.code', '=', 'his.school_type_code')
					->where('mcd.category_code', '=', 'SchoolType');
			})
			->select('his.school_name', 'his.entry_day', 'his.graduate_day', 'his.speciality', 'mcd.detail')
			->where('staff_code', $staffCode)
			->orderBy('his.entry_day', 'asc')
			->get();

		$careers = DB::table('person_career_history')
			->select('staff_code', 'id', 'company_name', 'job_type_detail', 'entry_day', 'retire_day')
			->where('staff_code', $staffCode)
			->orderBy('entry_day', 'asc')
			->get();

		//dd($careers);

		$styleArray = [
			'borders' => [
				'allBorders' => [
					'borderStyle' => Border::BORDER_THIN,
				],
			],
			'font' => [
				'name' => 'IPA Mincho', //  ''
			],

		];

		$styleArray2 = [
			'font' => [
				'name' => 'IPA Mincho', //  ''
			],
		];


		//$spreadsheet->/ 📌 **2行目からデータを書き込む**
		$row = 14;
		foreach ($users as $user) {
			$paddress = $user->city . $user->town . $user->address;
			$birthyear = substr($user->birthday, 0, 4);
			$birthmonth = substr($user->birthday, 5, 2);
			$bday = substr($user->birthday, 8, 2);
			$personAge = Carbon::parse($user->birthday)->age;
			//$gender = $user->sex=='1'  ? '　〇　　' : '　　〇　';
			$gender = $user->sex == '1'  ? '男' : '　女';
			$syear = date('Y');
			$smonth = date('m');
			$sday = date('d');
			$postno = $user->post_u . '-' . $user->post_l;
			$sheet->setCellValue("F7", $syear);
			$sheet->setCellValue("J7", $smonth);
			$sheet->setCellValue("L7", $sday);
			$sheet->getStyle("F7:L7")->applyFromArray($styleArray2);
			$sheet->setCellValue("A24", $birthyear);
			$sheet->setCellValue("D24", $birthmonth);
			$sheet->setCellValue("F24", $bday);
			//$sheet->setCellValue("M24", $gender);
			$sheet->setCellValue("J24", $personAge);
			$sheet->getStyle("A24:J24")->applyFromArray($styleArray2);
			$sheet->setCellValue("B{$row}", $user->name);
			$sheet->getStyle("B{$row}")->applyFromArray($styleArray2);
			$sheet->setCellValue("B10", $user->name_f);
			$sheet->getStyle("B10")->applyFromArray($styleArray2);
			$sheet->setCellValue("A36", $paddress);
			$sheet->getStyle("A36")->applyFromArray($styleArray2);
			$sheet->setCellValue("C32", $postno);
			$sheet->getStyle("C32")->applyFromArray($styleArray2);
			$sheet->setCellValue("N40", $user->portable_telephone_number);
			$sheet->getStyle("N40")->applyFromArray($styleArray2);
			$sheet->setCellValue("N32", $user->home_telephone_number);
			$sheet->getStyle("N32")->applyFromArray($styleArray2);
			$sheet->setCellValue("N56", $user->mail_address);
			$sheet->getStyle("N56")->applyFromArray($styleArray2);
			$row++;
		}

		$row = 66;
		if ($schools) {
			$sheet->setCellValue("C{$row}", '学　歴');
			$objStyle = $sheet->getStyle("C{$row}");
			// 中央寄せ
			$objStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
			$row += 5;
			foreach ($schools as $school) {
				if ((strpos($school->school_name, '高校') == false) &&  (strpos($school->school_name, '高等学校') == false) &&  (strpos($school->school_name, '大学') == false)) {
					$schoolTemp = $school->school_name . $school->detail;
				} else {
					$schoolTemp = $school->school_name;
				}
				$schEntryYear = substr($school->entry_day, 0, 4);
				$schEntryMonth = substr($school->entry_day, 5, 2);
				$schGradYear = substr($school->graduate_day, 0, 4);
				$schGradMonth = substr($school->graduate_day, 5, 2);
				//$schoolName = $school->school_name . $school->speciality;
				$schoolName = $schoolTemp . $school->speciality;
				$sheet->setCellValue("A{$row}", $schEntryYear);
				$sheet->setCellValue("B{$row}", $schEntryMonth);
				$sheet->setCellValue("C{$row}", $schoolName . '入学');
				$sheet->getStyle("A{$row}:C{$row}")->applyFromArray($styleArray2);
				$row += 5;
				$sheet->setCellValue("A{$row}", $schGradYear);
				$sheet->setCellValue("B{$row}", $schGradMonth);
				$sheet->setCellValue("C{$row}", $schoolName . '卒業');
				$sheet->getStyle("A{$row}:C{$row}")->applyFromArray($styleArray2);
				$row += 5;
			} //end foreach
		} // end if($schools)

		if ($careers) {
			$sheet->setCellValue("C{$row}", '職　歴');
			$objStyle = $sheet->getStyle("C{$row}");
			// 中央寄せ
			$objStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
			$row += 5;

			foreach ($careers as $career) {
				//dd($career);
				$entryYear = substr($career->entry_day, 0, 4);
				$entryMonth = substr($career->entry_day, 5, 2);
				if ($career->retire_day == '0000-00-00 00:00:00') {
					$retireYear = '';
					$retireMonth = '';
				} else {
					$retireYear  = substr($career->retire_day, 0, 4);
					$retireMonth = substr($career->retire_day, 5, 2);
				}

				$sheet->setCellValue("A{$row}", $entryYear);
				$sheet->setCellValue("B{$row}", $entryMonth);
				$sheet->setCellValue("C{$row}", $career->company_name . '入社');
				if ($row < 146) {
					$row += 5;
					$sheet->getStyle("A{$row}:C{$row}")->applyFromArray($styleArray2);
				} elseif ($row == 146) {
					$row += 6;
				} else {
					$row++;
				}

				if ($retireYear != '') {
					//dd($row . ' ' . $retireYear);
					$sheet->setCellValue("A{$row}", $retireYear);
					$sheet->setCellValue("B{$row}", $retireMonth);
					$sheet->setCellValue("C{$row}", $career->company_name . '退社');
				} else {
					//dd($retireYear);//$row +=5;
					$sheet->setCellValue("C{$row}", $career->company_name . '　現在に至る');
				}
				if ($row < 146) { //151
					$row += 5;
					$sheet->getStyle("A{$row}:C{$row}")->applyFromArray($styleArray2);
				} elseif ($row == 146) {
					$row += 6;
					$sheet->getStyle("A{$row}:O{$row}")->applyFromArray($styleArray);
				} else {
					$row++;
					$sheet->getStyle("A{$row}:O{$row}")->applyFromArray($styleArray);
				}
				//$row +=5;
			} //end foreach
		} //end if ($careers)
		// 幅は1ページに、縦は自動（0）
		$sheet->getPageSetup()->setFitToWidth(1);
		$sheet->getPageSetup()->setFitToHeight(0);

		// 余白も調整すると収まりがよくなります
		$sheet->getPageMargins()->setTop(0.5);
		$sheet->getPageMargins()->setBottom(0.5);
		$sheet->getPageMargins()->setLeft(0.3);
		$sheet->getPageMargins()->setRight(0.3);
		//次のシートに移動
		$spreadsheet->setActiveSheetIndex(1);

		//資格 //master_license
		$licenses = DB::table('person_license as pl')
			->join('master_license as ml', function ($join) {
				$join->on('pl.group_code', '=', 'ml.group_code')->on('pl.category_code', '=', 'ml.category_code')
					->on('pl.code', '=', 'ml.code');
			})
			->select(
				'pl.staff_code',
				'pl.id',
				'ml.group_name',
				'ml.category_name',
				'ml.name',
				'pl.get_day',
				'pl.created_at',
				'pl.update_at'
			)
			->where('staff_code', $staffCode)
			->get();
		//dd($licenses);

		//自己PR
		$prs = DB::table('person_self_pr')
			->select('staff_code', 'self_pr')
			->where('staff_code', $staffCode)
			->get();

		//スキル
		$skills = DB::table('person_skill')
			->join('master_code', function ($join) {
				$join->on('person_skill.category_code', '=', 'master_code.category_code')->on('person_skill.code', '=', 'master_code.code');
			})
			->select('person_skill.staff_code', 'master_code.detail', 'person_skill.start_day')
			->where('staff_code', $staffCode)
			->get();

		$sheet = $spreadsheet->getActiveSheet(); // テンプレートのシートを取得
		$row = 7;
		if ($licenses) {
			foreach ($licenses as $license) {
				$licenseYear = substr($license->get_day, 0, 4);
				$licenseMonth = substr($license->get_day, 5, 2);
				$licenseName = $license->group_name . $license->category_name  . $license->name;

				$sheet->setCellValue("A{$row}", $licenseYear);
				$sheet->setCellValue("B{$row}", $licenseMonth);
				$sheet->setCellValue("C{$row}", $licenseName);
				$sheet->getStyle("A{$row}:C{$row}")->applyFromArray($styleArray2);
				$row += 5;
			}
		}
		$row = 59;
		if ($prs) {
			foreach ($prs as $pr) {
				$prStr = str_replace(PHP_EOL, '', $pr->self_pr);
				$prArr = mb_str_split($prStr, 52); //48
				//$sheet->setCellValue("A{$row}", $pr->self_pr);
				foreach ($prArr as $prRow) {
					$sheet->setCellValue("A{$row}", $prRow);
					$sheet->getStyle("A{$row}")->applyFromArray($styleArray2);
					$row += 4;
					if ($row > 75) {
						break;
					}
				}
				//$sheet->setCellValue("A{$row}", $pr->self_pr);
			} // end foreach
		}
		//$personAge = Carbon::parse($user->birthday)i->age;	
		$row = 83;
		//dd($skills);
		if ($skills) {
			foreach ($skills as $skill) {
				if ($skill->start_day != '0000-00-00 00:00:00') {
					$skillYear = Carbon::parse($skill->start_day)->age;
				} else {
					$skillYear = '';
				}
				$sheet->setCellValue("A{$row}", $skill->detail . ' 経験年数:' . $skillYear . '年');
				$sheet->getStyle("A{$row}")->applyFromArray($styleArray2);
			}
		}

		$wishMotives = DB::table('person_resume_other')
			->select('staff_code', 'wish_motive', 'hope_column', 'commute_time')
			->where('staff_code', $staffCode)
			->first();

		$row = 107; //108;
		//dd($wishMotives);
		if ($wishMotives) {
			//foreach ($wishMotives as $motive) {
			$motiveTemp = preg_replace('/\r\n|\r|\n/', '',  $wishMotives->wish_motive);
			$motiveStr = str_replace(PHP_EOL, '', $motiveTemp); //$wishMotives->wish_motive);
			$motiveArr = mb_str_split($wishMotives->wish_motive, 35); //40//32
			//dd($motiveArr);
			foreach ($motiveArr as $motiveRow) {
				$sheet->setCellValue("A{$row}", $motiveRow);
				$sheet->getStyle("A{$row}")->applyFromArray($styleArray2);
				$row += 4; //5;
			}
			if ($wishMotives->commute_time > 0) {
				$commuteHour = floor($wishMotives->commute_time / 60);
				$commuteMinites = $wishMotives->commute_time % 60;
			} else {
				$commuteHour = Null;
				$commuteMinites = 0;
			}
			//dd($commuteHour. ' ' . $commuteMinites);
			$sheet->setCellValue("L108", $commuteHour);
			$sheet->setCellValue("N108", $commuteMinites);

			//dd($row);
			$row = 135; //185;
			$hopeArr = mb_str_split($wishMotives->hope_column, 52); //50
			foreach ($hopeArr as $hopeRow) {
				//dd($row . $hopeRow);
				$sheet->setCellValue("A{$row}", $hopeRow);
				$sheet->getStyle("A{$row}")->applyFromArray($styleArray2);
				$row += 4; // 5;
			}
		}
		foreach ($users as $user) {
			$row = 117;
			$sheet->setCellValue("M{$row}", $user->dependent_number);
			$row = 126;
			if ($user->marriage_flag == "1") {
				$marrige = '有';
			} else {
				$marrige = '無';
			}
			if ($user->dependent_flag == "1") {
				$dependent = '有'; // ✅ 正しいコード
			} else {
				$dependent = '無';
			}
		} // end foreach
		$row = 126;

		$sheet->setCellValue("K{$row}", $marrige);
		$sheet->setCellValue("O{$row}", '');
		$sheet->setCellValue("M{$row}", $dependent);


		$spreadsheet->setActiveSheetIndex(0);
		$spreadsheet->getActiveSheet()->freezePane('B2');
		//$spreadsheet -> getPageSetup() -> setScale(90);
		//$spreadsheet -> getPageSetup() -> setPaperSize(PageSetup::PAPERSIZE_A4); //A4サイズ
		// 📌 **ファイルを保存**
		//$exportPath = storage_path('app/exports/custom_export.xlsx');
		//$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
		//$writer->save($exportPath);

		// 📌 **エクスポート用にスプレッドシートを設定**
		//$event->writer->setSpreadsheet($spreadsheet);
		// 📌 **ファイルを保存**
		$exportPath = storage_path('app/exports/resume-' . $staffCode . '.xlsx');
		//$exportPath = storage_path('app/exports/custom_export.xlsx');
		$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
		$writer->save($exportPath);
		Log::info("Save file: {$exportPath}");
		//end makeExcel //20250207
		// $exportPath = storage_path('app/exports/resume-' . $staffCode . '.xlsx');
		//$exportPath = storage_path('app/exports/custom_export.xlsx');
		//dd($exportPath .  "\n" . 'file exist=  ' . (file_exists($exportPath)));

		// Pdf出力
		//dd(file_exists($exportPath));

		//exec('which libreoffice 2>&1', $output, $returnVar);
		//dd('LibreOffice のパス: ' . implode("\n", $output));
		//Log::info('LibreOffice のパス: ' . implode("\n", $output));


		if (file_exists($exportPath)) {
			$export_pdf_path = storage_path('app/exports/pdf');
			//dd($exportPath);
			//export HOME=/tmp; /usr/bin/soffice --headless --convert-to pdf:writer_pdf_Export --outdir /path/to/output --infilter="FilterOptions=PageRange=1;UseLosslessCompression=true;ReduceImageResolution=true;MaxImageResolution=300;PageSize=A4;Landscape" /path/to/input.xlsx

			// $outputPdf = $export_pdf_path . '/resume-' . $staffCode . '.pdf'; //出力PDFファイル
			$pdfPath = storage_path('app/exports/pdf/resume-' . $staffCode . '.pdf');
			//$outputPdf = $export_pdf_path . '/custom_export.pdf'; // 出力PDFファイル
			//$cmd = ['export HOME=/tmp; unoconv -f pdf -o '. $export_pdf_path . '/custom_export.pdf -e PageRange=1 -e ScaleToPagesX=1 -e ScaleToPagesY=0 ' . $exportPath];

			putenv('JAVA_HOME=/usr/lib/jvm/java-17-openjdk-17.0.14.0.7-2.el9.alma.1.x86_64');
			putenv('PATH=' . getenv('JAVA_HOME') . '/bin:' . '/usr/local/texlive/2024/bin/x86_64-linux/;' . getenv('PATH'));

			//dd($exportPath);
			// $cmd = 'export HOME=/tmp; /usr/bin/libreoffice --headless --convert-to pdf:"calc_pdf_Export:FilterOptions=ScaleToPagesX=1;ScaleToPagesY=0" --outdir "/var/www/html/shigotonavi/storage/app/exports/pdf" ' . escapeshellarg($exportPath) . ' ';
			$cmd = 'export HOME=/tmp; /usr/bin/libreoffice --headless --convert-to pdf:"calc_pdf_Export:FilterOptions=ScaleToPagesX=1;ScaleToPagesY=0" --outdir ' . escapeshellarg(dirname($pdfPath)) . ' ' . escapeshellarg($exportPath);
			// 
			//$cmd = 'export HOME=/tmp; /usr/bin/libreoffice --headless --convert-to pdf:calc_pdf_Export:"FilterOptions=PageRange=1;ScaleToPagesX=1;ScaleToPagesY=1" --outdir "/var/www/html/shigotonavi/storage/app/exports/pdf" "/var/www/html/shigotonavi/storage/app/exports/custom_export.xlsx"';
			//dd($cmd);
			//if (file_exists($exportPath)) {
			exec($cmd . " 2>&1", $output, $returnVar);
			//exec('env 2>&1', $output);
			//dd('環境変数一覧: ' . implode("\n", $output)); //Log::info('環境変数一覧: ' . implode("\n", $output));
			Log::info('LibreOffoce  Cmd: ' . $cmd);
			//dd('LibreOffice 戻り値: ' . $returnVar);
			Log::info('LibreOffice 出力: ' . implode("\n", $output));
			Log::info('LibreOffice 戻り値: ' . $returnVar);
			//$process = new Process($cmd);
			//$process->run();
			//}

			// ✅ 一時画像ファイルの削除
		    // ... LibreOffice export code ...

			if ($tempPath && file_exists($tempPath)) {
			    unlink($tempPath);
			    Log::info("🧹 Temp image file deleted: {$tempPath}");
			}

			// dd($output);
			// PDFをA4縦で全列1ページに縮小
			$resizeCommand =  "/usr/local/texlive/2024/bin/x86_64-linux/pdfjam  --trim '10mm 10mm 10mm 10mm' --clip true  --paper a4paper --scale 0.95 --outfile $pdfPath  --no-tidy --builddir /tmp   $export_pdf_path. '/resume-' . $staffCode . '.pdf' //custom_export.pdf"; //Processのときは[]
			//dd($resizeCommand);
			//$process2 = new Process($resizeCommand);
			//exec('export PATH=/usr/local/texlive/2024/bin/x86_64-linux:$PATH; which pdflatex 2>&1', $outputp, $return_varp);
			//dd('pdflatex 出力: ' . implode("\n", $outputp) . "\n" . 'pdflatex 戻り値: ' . $return_varp);
			//Log::info('pdflatex 出力: ' . implode("\n", $outputp));
			//Log::info('pdflatex 戻り値: ' . $return_varp);


			//$process2 = Process::fromShellCommandline($resizeCommand);
			//if ($returnVar) {
			//if ($process->isSuccessful()) {
			//exec($resizeCommand . " 2>&1", $outputr, $returnVarr);
			//dd('resize 出力: ' . implode("\n", $outputr) );
			//dd('resize 戻り値: ' . $returnVarr);
			//Log::info('resize 出力: ' . implode("\n", $outputr));
			//Log::info('resize 戻り値: ' . $returnVarr);
			//	//$process2->run();
			//Log::error('PDF resize失敗: ' . $returnVarr); //$process2->getErrorOutput());
			//   }


		}
	}

	public function makeCareerSheet($file_name)
	{
		$staffCode = Auth::user()->staff_code; //245Line

		DB::enableQueryLog();
		// Excel出力
		// 📌 **テンプレートを読み込む*
		$templatePath = storage_path('app/templates/careersheet.xlsx');
		$spreadsheet = IOFactory::load($templatePath);
		$sheet = $spreadsheet->getActiveSheet(); // テンプレートのシートを取得

		// 📌 **DBからデータを取得**
		$users = DB::table('master_person')
			->select(
				'staff_code',
				'name',
				'mail_address',
				'name_f',
				'home_telephone_number',
				'portable_telephone_number',
				'birthday',
				'city',
				'town',
				'address',
				'sex',
				'post_u',
				'post_l'
			)
			->where('staff_code', $staffCode)
			->get();
		$careers = DB::table('person_career_history as pch')
			->leftJoin('master_code as mcd', function ($join) {
				$join->on('pch.industry_type_code', '=', 'mcd.code')
					->where('mcd.category_code', 'IndustryType');
			})
			->leftJoin('master_code as mc2', function ($join2) {
				$join2->on('pch.job_type_code', '=', 'mc2.code')
					->where('mc2.category_code', 'JobType');
			})
			->select(
				'pch.staff_code',
				'pch.id',
				'pch.job_type_detail',
				'pch.company_name',
				'pch.capital',
				'pch.business_detail',
				'pch.job_type_detail',
				'pch.entry_day',
				'pch.retire_day',
				'mcd.detail as industry_type',
				'mc2.detail as job_type',
				'pch.number_employees as num'
			)
			->where('staff_code', $staffCode)
			->get();

		$prs = DB::table('person_self_pr')
			->select('staff_code', 'self_pr')
			->where('staff_code', $staffCode)
			->get();

		$wishMotives = DB::table('person_resume_other')
			->select('staff_code', 'wish_motive')
			->where('staff_code', $staffCode)
			->get();

		//dd(DB::getQueryLog());
		//dd($careers);
		$spreadsheet = IOFactory::load($templatePath);
		$sheet = $spreadsheet->getActiveSheet(); // テンプレートのシートを取得
		// 📌 **2行目からデータを書き込む**
		$sheet = $spreadsheet->getActiveSheet(); // テンプレートのシートを取得
		//dd($users[0]->name);
		$row = 4;
		$sheet->setCellValue("G{$row}", $users[0]->name);
		$row = 7;
		if ($careers) {
			foreach ($careers as $career) {
				$entryMonth = substr($career->entry_day, 0, 4) . '年' . substr($career->entry_day, 5, 2) . '月'; //
				if (!$career->retire_day || $career->retire_day == '0000-00-00 00:00:00') {
					$retireMonth = '現在に至る';
				} else {
					$retireMonth = substr($career->retire_day, 0, 4) . '年' . substr($career->retire_day, 5, 2) . '月';
					//$retireMonth = Carbon::$career->retire_day->format('Y年m月');
				} //edn if 
				$sheet->setCellValue("G10", '');
				$businessDetail = ''; //mb_convert_kana($career->business_detail,  'NA');
				$detailArr = [];
				if ($career) {
					//$businessDetail = $career->business_datail;
					$detailArr = mb_str_split($career->business_detail, 35);
					//$detailArr = mb_str_split($businessDetail, 35); //$career->business_detail, 35);
				}
				++$row;
				$sheet->mergeCells("A{$row}:I{$row}");
				$sheet->setCellValue("A{$row}", '(' . $career->company_name . $entryMonth . '～' . $retireMonth . ')');
				++$row;
				$sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Align::HORIZONTAL_DISTRIBUTED); //均等割付
				$sheet->setCellValue("B{$row}", '業　　種');
				$sheet->setCellValue("D{$row}", $career->industry_type);
				++$row;
				$sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Align::HORIZONTAL_DISTRIBUTED); //均等割付
				$sheet->setCellValue("B{$row}", '職　　種');
				$sheet->setCellValue("D{$row}", $career->job_type_detail);
				++$row;
				$sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Align::HORIZONTAL_DISTRIBUTED); //均等割付
				$sheet->setCellValue("B{$row}", '資 本 金');
				if ($career->capital > 0) {
					$sheet->setCellValue("D{$row}", $career->capital . '円'); //->job_type);
				}
				//++$row;
				$sheet->getStyle("G{$row}")->getAlignment()->setHorizontal(Align::HORIZONTAL_DISTRIBUTED); //均等割付
				$sheet->setCellValue("G{$row}", '従業員数');
				if ($career->num > 0) {
					$sheet->setCellValue("I{$row}", $career->num . '人');
				}
				++$row; //$row += 4
				//dd($detailArr);
				foreach ($detailArr as $key => $detail) {
					$detail = str_replace('\n', '', $detail);
					//if (strpos($detail, "\n") !== false) {
					//	$lfFlag = 1;
					//} else {
					//	$lfFlag = 2;
					//}
					$sheet->setCellValue("B{$row}", $detail);
					$row += 1; //$lfFlag; //$row +=2;//++$row; //+= $key;
				} //en for each
			}
		} else {
			$row = 16;
		} //end if ($careers)

		//dd(count($careers) . ' ' . $careers);
		if (count($careers) == 0) {
			$row = 16;
		}
		++$row;
		//自己PR
		if ($prs) {
			$sheet->setCellValue("B{$row}", '＜自己PR＞');
			++$row;
			foreach ($prs as $prRec) {
				$selfPrs = $prRec->self_pr;
				$selfPrs = str_replace("\n", "", $selfPrs);
				$prArr = mb_str_split($selfPrs, 35);
				foreach ($prArr as $pr) {
					$sheet->setCellValue("B{$row}", $pr);
					++$row;
				}
			} //end foreach
		} //end if

		//志望動機
		++$row;
		if ($wishMotives) {
			$sheet->setCellValue("B{$row}", '＜志望動機＞');
			++$row;
			foreach ($wishMotives as $motive) {
				$motiveArr = mb_str_split($motive->wish_motive, 35);
				foreach ($motiveArr as $motiveStr) {
					$sheet->setCellValue("B{$row}", $motiveStr);
					++$row;
				} //end foreach
			} //end foreach

		} //end if
		$sheet->setCellValue("I{$row}", '以　上');

		//}

		// 📌 **ファイルを保存**
		$exportPath = storage_path('app/exports/careersheet-' . $staffCode . '.xlsx');
		//$exportPath = storage_path('app/exports/custom_export.xlsx');
		$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
		$writer->save($exportPath);

		$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
		$writer->save($exportPath);

		return collect([]);
	}

	public function makeCareerPdf($file_name)
	{

		$staffCode = Auth::user()->staff_code; //245Line

		DB::enableQueryLog();
		// Excel出力
		// 📌 **テンプレートを読み込む*
		$templatePath = storage_path('app/templates/careersheet.xlsx');
		$spreadsheet = IOFactory::load($templatePath);
		$sheet = $spreadsheet->getActiveSheet(); // テンプレートのシートを取得

		// 📌 **DBからデータを取得**
		$users = DB::table('master_person')
			->select(
				'staff_code',
				'name',
				'mail_address',
				'name_f',
				'home_telephone_number',
				'portable_telephone_number',
				'birthday',
				'city',
				'town',
				'address',
				'sex',
				'post_u',
				'post_l'
			)
			->where('staff_code', $staffCode)
			->get();
		$careers = DB::table('person_career_history as pch')
			->leftJoin('master_code as mcd', function ($join) {
				$join->on('pch.industry_type_code', '=', 'mcd.code')
					->where('mcd.category_code', 'IndustryType');
			})
			->leftJoin('master_code as mc2', function ($join2) {
				$join2->on('pch.job_type_code', '=', 'mc2.code')
					->where('mc2.category_code', 'JobType');
			})
			->select(
				'pch.staff_code',
				'pch.id',
				'pch.job_type_detail',
				'pch.company_name',
				'pch.capital',
				'pch.business_detail',
				'pch.job_type_detail',
				'pch.entry_day',
				'pch.retire_day',
				'mcd.detail as industry_type',
				'mc2.detail as job_type',
				'pch.number_employees as num'
			)
			->where('staff_code', $staffCode)
			->get();
		$prs = DB::table('person_self_pr')
			->select('staff_code', 'self_pr')
			->where('staff_code', $staffCode)
			->get();

		$wishMotives = DB::table('person_resume_other')
			->select('staff_code', 'wish_motive')
			->where('staff_code', $staffCode)
			->get();

		//dd(DB::getQueryLog());
		//dd($careers);
		$spreadsheet = IOFactory::load($templatePath);
		$sheet = $spreadsheet->getActiveSheet(); // テンプレートのシートを取得
		//$spreadsheet->/ 📌 **2行目からデータを書き込む**
		$sheet = $spreadsheet->getActiveSheet(); // テンプレートのシートを取得
		$styleArray2 = [
			'font' => [
				'name' => 'IPA Mincho', //  ''
			],
		];

		//dd($users[0]->name);
		$sheet->setCellValue("G10", '');
		$sheet->getStyle("A1")->applyFromArray($styleArray2);
		$row = 4;
		$sheet->setCellValue("G{$row}", $users[0]->name);
		$sheet->getStyle("F{$row}:G{$row}")->applyFromArray($styleArray2);
		$row = 7;
		if ($careers) {
			foreach ($careers as $career) {
				$entryMonth = substr($career->entry_day, 0, 4) . '年' . substr($career->entry_day, 5, 2) . '月'; //Carbon::$career->entry_day->format('Y年m月');
				//dd($career);
				if (!$career->retire_day  || $career->retire_day == '0000-00-00 00:00:00') {
					$retireMonth = '現在に至る';
				} else {
					$retireMonth = substr($career->retire_day, 0, 4) . '年' . substr($career->retire_day, 5, 2) . '月';
					//$retireMonth = Carbon::$career->retire_day->format('Y年m月');
				} // end if
				$businessDetail = $career->business_detail; //mb_convert_kana($career->business_detail ,  'NA');
				$businessDetail = str_replace(PHP_EOL, '', $businessDetail);
				//dd($businessDetail);  
				$detailArr = mb_str_split($businessDetail, 35); //$career->business_detail, 35);
				//dd($detailArr);
				++$row;
				$sheet->mergeCells("A{$row}:I{$row}");
				$sheet->setCellValue("A{$row}", '(' . $career->company_name . $entryMonth . '～' . $retireMonth . ')');
				++$row;
				$sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Align::HORIZONTAL_DISTRIBUTED); //均等割付
				$sheet->setCellValue("B{$row}", '業　　種');
				$sheet->setCellValue("D{$row}", $career->industry_type);
				$sheet->getStyle("A{$row}:D{$row}")->applyFromArray($styleArray2);
				++$row;
				$sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Align::HORIZONTAL_DISTRIBUTED); //均等割付
				$sheet->setCellValue("B{$row}", '職　　種');
				$sheet->setCellValue("D{$row}", $career->job_type_detail);
				$sheet->getStyle("A{$row}:D{$row}")->applyFromArray($styleArray2);
				++$row;
				$sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Align::HORIZONTAL_DISTRIBUTED); //均等割付
				$sheet->setCellValue("B{$row}", '資 本 金');
				if ($career->capital > 10000) {
					$capital = $career->capital / 10000;
					$sheet->setCellValue("D{$row}", $capital . '万円');
				} elseif ($career->capital > 0) {
					$capital = $career->capital;
					$sheet->setCellValue("D{$row}", $capital . '円');
				} else {
					$capital = 0;
				}
				$sheet->getStyle("A{$row}:D{$row}")->applyFromArray($styleArray2);
				//$sheet->setCellValue("D{$row}", $capital . '万円'); //->job_type);
				//++$row;
				$sheet->getStyle("G{$row}")->getAlignment()->setHorizontal(Align::HORIZONTAL_DISTRIBUTED); //均等割付
				$sheet->setCellValue("G{$row}", '従業員数');
				if ($career->num > 0) {
					$sheet->setCellValue("I{$row}", $career->num . '人');
				}

				++$row; //$row += 4;
				//dd($detailArr);
				++$row;
				foreach ($detailArr as $key => $detail) {
					$detail = str_replace('\n', '', $detail);

					//$detail = str_replace('\n' , '', $detail);//改行を取る;
					//if (strpos($detail, "\n") !== false) {
					//	$detail = str_replace("\n", "", $detail);
					//	//dd($detail);
					//	$lfFlag = 1; //2;//2;
					//} else {
					//	$lfFlag = 1; //1;
					//}
					$sheet->setCellValue("B{$row}", $detail);
					$sheet->getStyle("B{$row}")->applyFromArray($styleArray2);
					$row += 1; //$lfFlag; //$row +=2; //++ $row; //
				}
				$row += 3;
			}
		} //end if ($careers)

		if (count($careers) == 0) {
			$row = 16;
		}
		++$row;
		//自己PR
		if ($prs) {
			$sheet->setCellValue("B{$row}", '＜自己PR＞');
			++$row;
			foreach ($prs as $prRec) {
				$selfPrs = $prRec->self_pr;
				$selfPrs = str_replace("\n", "", $selfPrs);
				$prArr = mb_str_split($selfPrs, 35);
				foreach ($prArr as $pr) {
					$sheet->setCellValue("B{$row}", $pr);
					$sheet->getStyle("B{$row}")->applyFromArray($styleArray2);
					++$row;
				}
			} //end foreach
		} //end if

		++$row;
		//志望動機
		if ($wishMotives) {
			$sheet->setCellValue("B{$row}", '＜志望動機＞');
			++$row;
			foreach ($wishMotives as $motive) {
				$motiveArr = mb_str_split($motive->wish_motive, 35);
				foreach ($motiveArr as $motiveStr) {
					$sheet->setCellValue("B{$row}", $motiveStr);
					$sheet->getStyle("B{$row}")->applyFromArray($styleArray2);
					++$row;
				} //end foreach
			} //end foreach

		} //end if

		$sheet->setCellValue("I{$row}", '以　上');

		//}

		$spreadsheet->getActiveSheet()->freezePane('B2');
		// 📌 **ファイルを保存**
		$exportPath = storage_path('app/exports/careersheet-' . $staffCode . '.xlsx');
		//$exportPath = storage_path('app/exports/custom_export.xlsx');
		$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
		$writer->save($exportPath);

		$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
		$writer->save($exportPath);

		$export_pdf_path = storage_path('app/exports/pdf');
		//dd($exportPath);

		$outputPdf = $export_pdf_path . '/careersheet-' . $staffCode . '.pdf'; //出力PDFファイル
		//$cmd = ['export HOME=/tmp; unoconv -f pdf -o '. $export_pdf_path . '/custom_export.pdf -e PageRange=1 -e ScaleToPagesX=1 -e ScaleToPagesY=0 ' . $exportPath];

		putenv('JAVA_HOME=/usr/lib/jvm/java-17-openjdk-17.0.14.0.7-2.el9.alma.1.x86_64');
		putenv('PATH=' . getenv('JAVA_HOME') . '/bin:' . '/usr/local/texlive/2024/bin/x86_64-linux/;' . getenv('PATH'));

		//dd($exportPath);
		$cmd = 'export HOME=/tmp; /usr/bin/libreoffice --headless --convert-to pdf:calc_pdf_Export:"FilterOptions=PageRange=1;ScaleToPagesX=1;ScaleToPagesY=0" --outdir "/var/www/html/shigotonavi/storage/app/exports/pdf" ' . $exportPath . ' ';

		//dd($cmd);
		//if (file_exists($exportPath)) {
		exec($cmd . " 2>&1", $output, $returnVar);
		//exec('env 2>&1', $output);
		//dd('環境変数一覧: ' . implode("\n", $output)); //Log::info('環境変数一覧: ' . implode("\n", $output));
		//dd($cmd . "\n" . '  libreoffice 出力: ' . implode("\n", $output) );
		//dd('LibreOffice 戻り値: ' . $returnVar);
		Log::info('careersheet LibreOffice 出力: ' . implode("\n", $output));
		Log::info('careersheet LibreOffice 戻り値: ' . $returnVar);
		//}
		if ($returnVar != 0) {
			dd("変換失敗" . $process->getErrorOutput());  //  ($process);
			Log::error('PDF 変換失敗: ' . implode("\n", $output)); //$process->getErrorOutput());
		} else {
			//dd($returnVar . ' ' . $outputPdf);
			Log::info('careersheet PDF 変換成功: ' . implode("\n", $output));
		}
	} //End of function

	//commnet out 20250322
	/*
        public function makeDailySheet($selectDate)
        {
   

                DB::enableQueryLog();
                // Excel出力
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet(); //
	
		$tNextDay = strtotime($selectDate . ' +1 day');	
		$nextDate = date("Y-m-d H:i:s",$tNextDay);
		$firstDate = $selectDate . ' 00:00:00';
		//dd($selectDate . '  '  . $nextDate);
 		$users = DB::table('master_person as mp')
			->leftJoin('person_offer as po' , 'mp.staff_code', '=', 'po.staff_code')
			->leftJoin('person_career_history as pc', 'mp.staff_code', '=', 'pc.staff_code')
			->leftJoin('log_person_signin as lps' , 'mp.staff_code', '=', 'lps.staff_code')
			->leftJoin('person_hope_job_type as phj', 'mp.staff_code', '=', 'phj.staff_code')
			->leftJoin('master_code as mc', function ($join) {
                     	    $join->on('mc.code', '=', 'mp.prefecture_code')
                            ->where('mc.category_code', '=', 'Prefecture');
			   } )			    
			->select('mp.created_at', 'mp.staff_code', 'mp.name', 'mp.birthday', 'mp.age', 'mp.sex', 'mc.detail as prefecture'
			  , 'mp.city', 'mp.portable_telephone_number as tel', 'pc.company_name', 'pc.job_type_detail as career_job'
			  , 'phj.job_type_detail as hope_job' , 'po.order_code as offer'
			  , 'lps.match_count', 'lps.update_count', 'lps.detail_count')
			->where('mp.created_at', '>=', $firstDate)
			->where('mp.created_at', '<', $nextDate)
			//->limit(5)
			->orderby('mp.created_at')
			->get(); 
		//dd(DB::getQueryLog());
		
		$cnt = 0;
	    if($users) {
		$sheet->setCellValue("A3", "No");
		$sheet->setCellValue("B3", "登録日時");
		$sheet->setCellValue("C3", "氏名");
		$sheet->setCellValue("D3", "誕生日");
		$sheet->setCellValue("E3", "年齢");
		$sheet->setCellValue("F3", "性別");
		$sheet->setCellValue("G3", "都道府県");
		$sheet->setCellValue("H3", "市町村");
		$sheet->setCellValue("I3", "電話");
		$sheet->setCellValue("J3", "直近勤務先");
		$sheet->setCellValue("K3", "経験職種");
		$sheet->setCellValue("L3", "希望職種");
		$sheet->setCellValue("M3", "オファー");
		$sheet->setCellValue("N3", "match数");
		$sheet->setCellValue("O3", "絞込数");
		$sheet->setCellValue("P3", "求人票閲覧数");
	    } //if ($users)
		    $row = 4;
		    $cnt = 1;
		foreach ($users as $user) {
		    	$sheet->setCellValue("A{$row}", $cnt);
			$sheet->setCellValue("B{$row}", $user->created_at);
			$sheet->setCellValue("C{$row}", $user->name);
			$sheet->setCellValue("D{$row}", $user->birthday);
			//$sheet->setCellValue("E{$row}", $user->);
			$sheet->setCellValue("E{$row}", $user->age);
			$sheet->setCellValue("F{$row}", $user->sex);
			$sheet->setCellValue("G{$row}", $user->prefecture);
			$sheet->setCellValue("H{$row}", $user->city);
			$sheet->setCellValue("I{$row}", $user->tel);
			$sheet->setCellValue("J{$row}", $user->company_name);
			$sheet->setCellValue("K{$row}", $user->career_job);
			$sheet->setCellValue("L{$row}", $user->hope_job);
			$sheet->setCellValue("M{$row}", $user->offer);
			$sheet->setCellValue("N{$row}", $user->match_count);
			$sheet->setCellValue("O{$row}", $user->update_count);
			$sheet->setCellValue("P{$row}", $user->detail_count); 
			//$sheet->setCellValue("Q{$row}",);
		   ++ $row;
		}// end foreach
	    //}// end if

                 $spreadsheet->getActiveSheet()->freezePane("B2");
                  // 📌 **ファイルを保存**
                  $exportPath = storage_path("app/exports/stafflist-" . $selectDate . ".xlsx");
                  $writer = IOFactory::createWriter($spreadsheet, "Xlsx");
                  $writer->save($exportPath);
  

	}// end function
	*/
} //End of Class

//
