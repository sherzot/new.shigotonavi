<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

//use App\Models\Item;


class CsvUploadController extends Controller
{

    public function showForm()
    {
        return view('agent.csv_upload');
    }


    public function uploadCsv(Request $request)
    {

        // CSVファイルが存在するかの確認
        if ($request->hasFile('csvFile')) {
            //拡張子がCSVであるかの確認
            if ($request->csvFile->getClientOriginalExtension() !== "csv") {
                throw new Exception('不適切な拡張子です。');
            }
		//dd($request->csvFile);
            //ファイルの保存
            $newCsvFileName = $request->csvFile->getClientOriginalName();
		//dd($newCsvFileName);
	    $request->csvFile->storeAs('csv', $newCsvFileName);
	    //$request->csvFile->storeAs('app/public/csv', $newCsvFileName);
        } else {
            throw new Exception('CSVファイルの取得に失敗しました。');
        }
	//保存したCSVファイルのPath
	$filePath = ("csv/{$newCsvFileName}");
	//$filePath = ("app/public/csv/{$newCsvFileName}");
	//dd($filePath);
        //保存したCSVファイルの取得
        //$csv = Storage::disk('local')->get("public/csv/{$newCsvFileName}");
	//return $filePath;
	$this->importToTable($filePath);

	return view('agent.csv_upload')->with('message', 'job_order更新が成功しました！');	

    }// end function

    // job_order Table Update and Insert
    public function importToTable($file_path)
    {
	$file_path = '/var/www/html/shigotonavi/storage/app/private/' . $file_path;
	//$file_path = '/var/www/html/shigotonavi/storage/app/private/' . $file_path;
	//dd('importToTable  ' . $file_path . ' ' .storage_path($file_path));	
        try {
            DB::beginTransaction();
            //移行先のDBを指定する
            DB::connection('mysql');//('new_connection');

            DB::table('job_order_back')
                ->delete();

               $columns = DB::getSchemaBuilder()->getColumnListing('job_order_back'); // `job_order` のカラム一覧を取得

                DB::table('job_order_back')->insertUsing(
                    $columns, // 挿入するカラム
                    DB::table('job_order as ji')
                       ->leftJoin('job_order_back as jb', 'ji.order_code', '=', 'jb.order_code')
                        ->whereNull('jb.order_code')
                         ->select('ji.*') // `job_order` の全カラムを選択
                );

	    DB::table('job_order_import')
		->delete();
	     DB::statement('SET SESSION MAX_EXECUTION_TIME = 1200000');//1200sec //20minits

            //外部キー制約無効
            DB::statement('SET foreign_key_checks = 0;');
            // CSV取り込み １合目はフィールド名なので無視
	    //$insert_sql = "LOAD DATA LOCAL INFILE '" . $file_path . "' INTO TABLE shigotonavie.csv_test "
            $insert_sql = "LOAD DATA LOCAL INFILE '" . $file_path . "' INTO TABLE job_order_import "
                . "FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"' IGNORE 1 LINES ";

            DB::statement($insert_sql);
	    Log::info('job_order_importに LOAD DATAが実行されました。' . $insert_sql);
            DB::statement('SET foreign_key_checks = 1;');

		//  // job_prder Update
		DB::table('job_order as jb')
    		->join('job_order_import as ji', 'jb.order_code', '=', 'ji.order_code')
    		->update([
		  	'jb.company_code' => DB::raw('ji.company_code'),
		  	'jb.regist_commit' => DB::raw('ji.regist_commit'),
		  	'jb.public_flag' => DB::raw('ji.public_flag'),
			'jb.public_day' => DB::raw('ji.public_day'),
			'jb.public_limit_day' => DB::raw('ji.public_limit_day'),
			'jb.recruitment_limit_day' => DB::raw('ji.recruitment_limit_day'),
			'jb.competition_flag' => DB::raw('ji.public_limit_day'),
			'jb.competition_remark' => DB::raw('ji.competition_remark'),
			'jb.client_class_flag' => DB::raw('ji.client_class_flag'),
			'jb.client_class_remark' => DB::raw('ji.client_class_remark'),
			'jb.order_condition_flag' => DB::raw('ji.order_condition_flag'),
			'jb.order_condition_remark' => DB::raw('ji.order_condition_remark'),
			'jb.access_count' => DB::raw('ji.access_count'),
			'jb.order_type' => DB::raw('ji.order_type'),
			'jb.order_progress_type' => DB::raw('ji.order_progress_type'),
			'jb.branch_code' => DB::raw('ji.branch_code'),
			'jb.employee_code' => DB::raw('ji.employee_code'),
			'jb.coordinator_code' => DB::raw('ji.coordinator_code'),
			'jb.job_type_detail' => DB::raw('ji.job_type_detail'),
			'jb.business_detail' => DB::raw('ji.business_detail'),
			'jb.hope_school_history_code' => DB::raw('ji.hope_school_history_code'),
			'jb.age_min' => DB::raw('ji.age_min'),
			'jb.age_max' => DB::raw('ji.age_max'),
			'jb.age_reason_flag' => DB::raw('ji.age_reason_flag'),
			'jb.age_min2_best' => DB::raw('ji.age_min2_best'),
			'jb.age_max2_best' => DB::raw('ji.age_max2_best'),
			'jb.yearly_income_min' => DB::raw('ji.yearly_income_min'),
			'jb.yearly_income_max' => DB::raw('ji.yearly_income_max'),
			'jb.monthly_income_min' => DB::raw('ji.monthly_income_min'),
			'jb.monthly_income_max' => DB::raw('ji.monthly_income_max'),
			'jb.daily_income_min' => DB::raw('ji.daily_income_min'),
			'jb.daily_income_max' => DB::raw('ji.daily_income_max'),
			'jb.hourly_income_min' => DB::raw('ji.hourly_income_min'),
			'jb.hourly_income_max' => DB::raw('ji.hourly_income_max'),
			'jb.percentage_pay_flag' => DB::raw('ji.percentage_pay_flag'),
			'jb.income_remark' => DB::raw('ji.income_remark'),
			'jb.experienced_flag1' => DB::raw('ji.income_remark'),
			'jb.experienced_industry_type_code1' => DB::raw('ji.experienced_industry_type_code1'),
			'jb.experienced_industry_type_period1' => DB::raw('ji.experienced_industry_type_period1'),
			'jb.experienced_job_type_code1' => DB::raw('ji.experienced_job_type_code1'),
			'jb.experienced_job_type_period1' => DB::raw('ji.experienced_job_type_period1'),
			'jb.age_min2' => DB::raw('ji.age_min2'),
			'jb.age_max2' => DB::raw('ji.age_max2'),
			'jb.yearly_income_min' => DB::raw('ji.yearly_income_min'),
			'jb.yearly_income_max2' => DB::raw('ji.yearly_income_max2'),
			'jb.monthly_income_min2' => DB::raw('ji.monthly_income_min2'),
			'jb.monthly_income_max2' => DB::raw('ji.monthly_income_max2'),
			'jb.daily_income_min2' => DB::raw('ji.daily_income_min2'),
			'jb.daily_income_max2' => DB::raw('ji.daily_income_max2'),
			'jb.hourly_income_min2' => DB::raw('ji.hourly_income_min2'),
			'jb.hourly_income_max2' => DB::raw('ji.hourly_income_max2'),
			'jb.percentage_pay_flag2' => DB::raw('ji.percentage_pay_flag2'),
			'jb.income_remark2' => DB::raw('ji.income_remark2'),
			'jb.experienced_flag2' => DB::raw('ji.experienced_flag2'),
			'jb.experienced_industry_type_code2' => DB::raw('ji.experienced_industry_type_code2'),
			'jb.experienced_industry_type_period2' => DB::raw('ji.experienced_industry_type_period2'),
			'jb.experienced_job_type_code2' => DB::raw('ji.experienced_job_type_code2'),
			'jb.experienced_job_type_period2' => DB::raw('ji.experienced_job_type_period2'),
			'jb.allowance' => DB::raw('ji.allowance'),
			'jb.work_time_remark' => DB::raw('ji.work_time_remark'),
			'jb.weekly_holiday_type' => DB::raw('ji.weekly_holiday_type'),
			'jb.holiday_remark' => DB::raw('ji.holiday_remark'),
			'jb.uniform_flag' => DB::raw('ji.uniform_flag'),
			'jb.uniform_size' => DB::raw('ji.uniform_size'),
			'jb.locker_flag' => DB::raw('ji.locker_flag'),
			'jb.employee_restaurant_flag' => DB::raw('ji.employee_restaurant_flag'),
			'jb.board_flag' => DB::raw('ji.board_flag'),
			'jb.smoking_flag' => DB::raw('ji.smoking_flag'),
			'jb.smoking_area_flag' => DB::raw('ji.smoking_area_flag'),
			'jb.duty_system_flag' => DB::raw('ji.duty_system_flag'),
			'jb.duty_type' => DB::raw('ji.duty_type'),
			'jb.duty_time_flag' => DB::raw('ji.duty_time_flag'),
			'jb.transfer_flag' => DB::raw('ji.transfer_flag'),
			'jb.over_work_flag' => DB::raw('ji.over_work_flag'),
			'jb.foreign_flag' => DB::raw('ji.foreign_flag'),
			'jb.working_place_companyname' => DB::raw('ji.working_place_companyname'),
			'jb.update_at' => DB::raw('ji.update_at'),
			'jb.jms_image_id' => DB::raw('ji.jms_image_id'),
		]);
		Log::info('job_order_import から job_order へのupdateが成功しました。');
		Log::info(DB::getQueryLog());

		// 不足しているレコードを Insert
		$columns = DB::getSchemaBuilder()->getColumnListing('job_order'); // `job_order` のカラム一覧を取得

		DB::table('job_order')->insertUsing(
		    $columns, // 挿入するカラム
		    DB::table('job_order_import as ji')
 		       ->leftJoin('job_order as jb', 'ji.order_code', '=', 'jb.order_code')
        		->whereNull('jb.order_code')
	       		 ->select('ji.*') // `job_order_import` の全カラムを選択
		);

	    /*
	    //DB::table('master_company as mc')
	//	->join('master_company_import as mi',	'mc.company_code',	'=',	'mi.company_code')
	//	->update([
	//	'mc.temp_code' => DB::raw('mi.temp_code'),               
	//	'mc.company_code_lis' => DB::raw('mi.company_code_lis'),        
	//	'mc.password' => DB::raw('mi.password'),       
	//	'mc.regist_commit' => DB::raw('mi.regist_commit'),            
	//	'mc.created_at' => DB::raw('mi.created_at'),              
	//	'mc.updated_at' => DB::raw('mi.updated_at'),              
	//	'mc.company_name_k' => DB::raw('mi.company_name_k'),           
	//	'mc.company_name_f' => DB::raw('mi.company_name_f'),          
	//	'mc.representative' => DB::raw('mi.representative'),          
	//	'mc.establish_year' => DB::raw('mi.establish_year'),          
	//	'mc.establish_month' => DB::raw('mi.establish_month'),         
	//	'mc.industry_type' => DB::raw('mi.industry_type'),           
	//	'mc.industry_type_name' => DB::raw('mi.industry_type_name'),       
	//	'mc.capital_amount' => DB::raw('mi.capital_amount'),      
	//	'mc.forein_capital' => DB::raw('mi.forein_capital'),          
	//	'mc.list_class' => DB::raw('mi.list_class'),              
	//	'mc.all_employee_num' => DB::raw('mi.all_employee_num'),        
	//	'mc.man_employee_num' => DB::raw('mi.man_employee_num'),        
	//	'mc.woman_employee_num' => DB::raw('mi.woman_employee_num'),      
	//	'mc.homepage_address' => DB::raw('mi.homepage_address'),       
	//	'mc.post__u' => DB::raw('mi.post__u'),                
	//	'mc.post__l' => DB::raw('mi.post__l'),                
	//	'mc.prefecture' => DB::raw('mi.prefecture'),              
	//	'mc.city_k' => DB::raw('mi.city_k'),                  
	//	'mc.city_f' => DB::raw('mi.city_f'),                  
	//	'mc.town' => DB::raw('mi.town'),                    
	//	'mc.address' => DB::raw('mi.address'),                
	//	'mc.telephone_number' => DB::raw('mi.telephone_number'),        
	//	'mc.rail_line_name1' => DB::raw('mi.rail_line_name1'),         
	//	'mc.rail_line_name_moji1' => DB::raw('mi.rail_line_name_moji1'),     
	//	'mc.station_code1' => DB::raw('mi.station_code1'),           
	//	'mc.station_name1' => DB::raw('mi.station_name1'),           
	//	'mc.work_or_bus1' => DB::raw('mi.work_or_bus1'),            
	//	'mc.work_bus_time1' => DB::raw('mi.work_bus_time1'),          
	//	'mc.rail_line_name2' => DB::raw('mi.rail_line_name2'),         
	//	'mc.rail_line_name_moji2' => DB::raw('mi.rail_line_name_moji2'),    
	//	'mc.station_code2' => DB::raw('mi.station_code2'),           
	//	'mc.station_name2' => DB::raw('mi.station_name2'),           
	//	'mc.work_or_bus2' => DB::raw('mi.work_or_bus2'),            
	//	'mc.work_bus_time2' => DB::raw('mi.work_bus_time2'),          
	//	'mc.travel_cost' => DB::raw('mi.travel_cost'),             
	//	'mc.traffic_cost_type' => DB::raw('mi.traffic_cost_type'),       
	//	'mc.month_traffic_cost' => DB::raw('mi.month_traffic_cost'),      
	//	'mc.society_insurance' => DB::raw('mi.society_insurance'),       
	//	'mc.sanatorium' => DB::raw('mi.sanatorium'),             
	//	'mc.enterprise_pension' => DB::raw('mi.enterprise_pension'),     
	//	'mc.wealth_shape' => DB::raw('mi.wealth_shape'),            
	//	'mc.stock_option' => DB::raw('mi.stock_option'),            
	//	'mc.retirement_pay' => DB::raw('mi.retirement_pay'),          
	//	'mc.residence_pay' => DB::raw('mi.residence_pay'),           
	//	'mc.family_pay' => DB::raw('mi.family_pay'),              
	//	'mc.employee_dormitory' => DB::raw('mi.employee_dormitory'),      
	//	'mc.company_house' => DB::raw('mi.company_house'),           
	//	'mc.new_employee_training' => DB::raw('mi.new_employee_training'),  
	//	'mc.overseas_training' => DB::raw('mi.overseas_training'),       
	//	'mc.other_training' => DB::raw('mi.other_training'),          
	//	'mc.welfare_program_remark' => DB::raw('mi.welfare_program_remark'),  
	//	'mc.transfer' => DB::raw('mi.transfer'),                
	//	'mc.business_contents' => DB::raw('mi.business_contents'),       
	//	'mc.company_pr' => DB::raw('mi.company_pr'),              
	//	'mc.business_other' => DB::raw('mi.business_other'),          
	//	'mc.is_send' => DB::raw('mi.is_send'),                
	//	'mc.publish_flg' => DB::raw('mi.publish_flg'),             
	//	'mc.contact_mail_address' => DB::raw('mi.contact_mail_address'),    
	//	'mc.contact_person_name' => DB::raw('mi.contact_mail_address'),      
	//	'mc.contact_section_name' => DB::raw('mi.contact_section_name'),   
	//	'mc.demand_prefecture' => DB::raw('mi.demand_prefecture'),      
	//	'mc.demand_city__k' => DB::raw('mi.demand_city__k'),          
	//	'mc.demand_city__f' => DB::raw('mi.demand_city__f'),          
	//	'mc.demand_town' => DB::raw('mi.demand_town'),             
	//	'mc.demand_address' => DB::raw('mi.demand_address'),          
	//	'mc.demand_person_name' => DB::raw('mi.demand_person_name'),      
	//	'mc.demand_section_name' => DB::raw('mi.demand_section_name'),     
	//	'mc.lis_person_code' => DB::raw('mi.lis_person_code'),         
	//	'mc.lis_person_name' => DB::raw('mi.lis_person_name'),         
	//	'mc.lis_mail_address' => DB::raw('mi.lis_mail_address'),        
	//	'mc.group_code' => DB::raw('mi.group_code'),              
	//	'mc.branch_code' => DB::raw('mi.branch_code'),            
	//	'mc.new_job_mail' => DB::raw('mi.new_job_mail'),           
	//	'mc.tanto1_yakusyoku' => DB::raw('mi.tanto1_yakusyoku'),       
	//	'mc.tanto2_name ' => DB::raw('mi.tanto2_name'),           
	//	'mc.tanto2_yakusyoku' => DB::raw('mi.tanto2_yakusyoku'),       
	//	'mc.mailaddr' => DB::raw('mi.mailaddr'),               
	//	'mc.keiyaku_ymd' => DB::raw('mi.keiyaku_ymd'),           
	//	'mc.company_kbn ' => DB::raw('mi.company_kbn'),           
	//	'mc.temp_permit_flag' => DB::raw('mi.temp_permit_flag'),       
	//	'mc.intro_permit_flag' => DB::raw(' mi.intro_permit_flag'),      
	//	'mc.simebi' => DB::raw('mi.simebi'),                 
	//	'mc.moshikomi_kbn ' => DB::raw('mi.moshikomi_kbn'),         
	//	'mc.moshikomi_kbn_t' => DB::raw('mi.moshikomi_kbn_t'),         
	//	'mc.new_person_mail' => DB::raw('mi.new_person_mail'),         
	//	'mc.kado_kbn' => DB::raw('mi.kado_kbn'),               
	//	'mc.kyujin_riyo_kbn' => DB::raw('mi.kyujin_riyo_kbn'),         
	//	'mc.company_syudan1_1' => DB::raw('mi.company_syudan1_1'),        
	//	'mc.company_syudan1_2' => DB::raw('mi.company_syudan1_2'),       
	//	'mc.company_syudan2_1' => DB::raw('mi.company_syudan2_1'),       
	//	'mc.company_syudan2_2' => DB::raw('mi.company_syudan2_2'),       
	//	'mc.kyujin_yuryo_flg' => DB::raw('mi.kyujin_yuryo_flg'),        
	//	'mc.keisai_riyo_flg' => DB::raw('mi.keisai_riyo_flg'),         
	//	'mc.lis_regist_day' => DB::raw('mi.lis_regist_day'),          
	//	'mc.sales_db_c_pass_flg' => DB::raw('mi.sales_db_c_pass_flg'),     
	//	'mc.intbase_contract_day' => DB::raw('mi.intbase_contract_day'),   
	//	'mc.sales_constitution_ratio' => DB::raw('mi.sales_constitution_ratio'),
	//	'mc.main_client' => DB::raw('mi.main_client'),            
	//	'mc.competitor' => DB::raw('mi.competitor'),              
	//	'mc.accounting_period1' => DB::raw('mi.accounting_period1'),      
	//	'mc.sales_ammount1' => DB::raw('mi.sales_ammount1'),         
	//	'mc.ordinary_profit1' => DB::raw('mi.ordinary_profit1'),        
	//	'mc.accounting_period2' => DB::raw('mi.accounting_period2'),      
	//	'mc.sales_amount2' => DB::raw('mi.sales_amount2'),          
	//	'mc.ordinary_profit2' => DB::raw('mi.ordinary_profit2'),       
	//	'mc.accounting_period3' => DB::raw('mi.accounting_period3'),      
	//	'mc.sales_amount3' => DB::raw('mi.sales_amount3'),          
	//	'mc.ordinary_profit3' => DB::raw('mi.ordinary_profit3'),        
	//	'mc.important_notice' => DB::raw('mi.important_notice'),       
	//	'mc.employee_average_age' => DB::raw('mi.employee_average_age'),    
	//	'mc.main_stock_holder' => DB::raw('mi.main_stock_holder'),      
	//	'mc.other ' => DB::raw('mi.other'),                 
	//	'mc.atmosphere' => DB::raw('mi.atmosphere'),              
          //      ]);
	   */
	    //Insert

	  //Company_Agent Insert

            DB::commit();
		Storage::delete($file_path);//file Delete

        } catch (Exception $e) {
            DB::rollback();
            //エラー処理
	    dd('Err: ' . $e);
        }
        return;
    }

//
    public function uploadCompanyCsv(Request $request)
    {

        // CSVファイルが存在するかの確認
        if ($request->hasFile('companyCsv')) {
            //拡張子がCSVであるかの確認
            if ($request->csvFile->getClientOriginalExtension() !== "csv") {
                throw new Exception('不適切な拡張子です。');
            }
                //dd($request->csvFile);
            //ファイルの保存
            $newCsvFileName = $request->csvFile->getClientOriginalName();
                //dd($newCsvFileName);
            $request->csvFile->storeAs('csv', $newCsvFileName);
            //$request->csvFile->storeAs('app/public/csv', $newCsvFileName);
        } else {
            throw new Exception('companyCSVファイルの取得に失敗しました。');
        }
        //保存したCSVファイルのPath
        $filePath = ("csv/{$newCsvFileName}");
        //dd($filePath);
        //保存したCSVファイルの取得
        //$csv = Storage::disk('local')->get("public/csv/{$newCsvFileName}");
        $this->importToMasterCompany($filePath);

        return view('agent.csv_upload')->with('message2', 'master_company更新が成功しました！');

    }// end function


   //importMastrCompany
    public function importToMasterCompany($file_path)
    {
        $file_path = '/var/www/html/shigotonavi/storage/app/private/' . $file_path;
        //$file_path = '/var/www/html/shigotonavi/storage/app/private/' . $file_path;
        //dd('importToTable  ' . $file_path . ' ' .storage_path($file_path));
        try {
	    DB::enableQueryLog();
            DB::beginTransaction();
            //移行先のDBを指定する
            DB::connection('mysql');//('new_connection');

            DB::statement('SET SESSION MAX_EXECUTION_TIME = 1200000');//1200sec //20minits

            DB::table('master_company_back')
                ->delete();

               $columns = DB::getSchemaBuilder()->getColumnListing('master_company_back'); // `job_order` のカラム一覧を取得

                DB::table('master_company_back')->insertUsing(
                    $columns, // 挿入するカラム
                    DB::table('master_company as mc')
                       ->leftJoin('master_company as mb', 'mc.company_code', '=', 'mb.company_code')
                        ->whereNull('jb.order_code')
                         ->select('mc.*') // `master_company` の全カラムを選択
                );

            DB::table('master_company_import')
                ->delete();

            //外部キー制約無効
            DB::statement('SET foreign_key_checks = 0;');
            // CSV取り込み
            //$insert_sql = "LOAD DATA LOCAL INFILE '" . $file_path . "' INTO TABLE shigotonavie.csv_test "
            $insert_sql = "LOAD DATA LOCAL INFILE '" . $file_path . "' INTO TABLE master_company_import "
                . "FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"' IGNORE 1 LINES ";

            DB::statement($insert_sql);
            DB::statement('SET foreign_key_checks = 1;');

	    // company_agent Insert
		//$columns = DB::getSchemaBuilder()->getColumnListing('company_agent'); // `company_agentr` のカラム一覧を
                DB::table('company_agent_import')->insertUsing(
                //    $columns,
		    'agent_code', 'company_code', // 挿入するカラム
                    DB::table('master_company as mc')
                       ->leftJoin('company_agent as ca', 'mc.company_code', '=', 'ca.company_code')
                        ->whereNull('ca.company_code')
                         ->select(DB::raw("'A0000001'"), 'mc.company_code') // `master_company`カラムを選択
                );

		//$columns = DB::getSchemaBuilder()->getColumnListing('company_agent'); // `company_agentr` のカラム一覧
                $columns = ['agent_code', 'company_code', 'password', 'resist_commit', 'created_at', 'update_at']; // `company_agent` の適切なカラムを定義
		DB::table('company_agent')->insertUsing(
		    $columns,
                    //('agent_code', 'company_code', 'password', 'resist_commit', 'created_at' ,'update_at' ), // 挿入するカラム
                    DB::table('company_agent_import as ci')
                       //->leftJoin('company_agent as ca', 'ci.company_code', '=', 'ca.company_code')
			        ->leftJoin('company_agent as ca', function ($join) {
		            $join->on('ci.company_code', '=', 'ca.company_code')
                		 ->on('ci.agent_code', '=', 'ca.agent_code'); // 複数カラムで存在チェック
        		})
                        ->whereNull('ca.company_code')
                         ->select('ci.agent_code', 'ci.company_code', 'ci.password', 'ci.resist_commit', 'ci.created_at', 'ci.update_at') // `company_agent_import` の全カラムを選択
                );

	    Log::info(DB::getQueryLog());


	    // master_company Update
             DB::table('master_company as mc')
                ->join('master_company_import as mi',   'mc.company_code',      '=',    'mi.company_code')
                ->update([
                'mc.temp_code' => DB::raw('mi.temp_code'),
                'mc.company_code_lis' => DB::raw('mi.company_code_lis'),
                'mc.password' => DB::raw('mi.password'),
                'mc.regist_commit' => DB::raw('mi.regist_commit'),
                'mc.created_at' => DB::raw('mi.created_at'),
                'mc.updated_at' => DB::raw('mi.updated_at'),
                'mc.company_name_k' => DB::raw('mi.company_name_k'),
                'mc.company_name_f' => DB::raw('mi.company_name_f'),
                'mc.representative' => DB::raw('mi.representative'),
                'mc.establish_year' => DB::raw('mi.establish_year'),
                'mc.establish_month' => DB::raw('mi.establish_month'),
                'mc.industry_type' => DB::raw('mi.industry_type'),
                'mc.industry_type_name' => DB::raw('mi.industry_type_name'),
                'mc.capital_amount' => DB::raw('mi.capital_amount'),
                'mc.forein_capital' => DB::raw('mi.forein_capital'),
                'mc.list_class' => DB::raw('mi.list_class'),
                'mc.all_employee_num' => DB::raw('mi.all_employee_num'),
                'mc.man_employee_num' => DB::raw('mi.man_employee_num'),
                'mc.woman_employee_num' => DB::raw('mi.woman_employee_num'),
                'mc.homepage_address' => DB::raw('mi.homepage_address'),
                'mc.post__u' => DB::raw('mi.post__u'),
                'mc.post__l' => DB::raw('mi.post__l'),
                'mc.prefecture' => DB::raw('mi.prefecture'),
                'mc.city_k' => DB::raw('mi.city_k'),
                'mc.city_f' => DB::raw('mi.city_f'),
                'mc.town' => DB::raw('mi.town'),
                'mc.address' => DB::raw('mi.address'),
                'mc.telephone_number' => DB::raw('mi.telephone_number'),
                'mc.rail_line_name1' => DB::raw('mi.rail_line_name1'),
                'mc.rail_line_name_moji1' => DB::raw('mi.rail_line_name_moji1'),
                'mc.station_code1' => DB::raw('mi.station_code1'),
                'mc.station_name1' => DB::raw('mi.station_name1'),
                'mc.work_or_bus1' => DB::raw('mi.work_or_bus1'),
                'mc.work_bus_time1' => DB::raw('mi.work_bus_time1'),
                'mc.rail_line_name2' => DB::raw('mi.rail_line_name2'),
                'mc.rail_line_name_moji2' => DB::raw('mi.rail_line_name_moji2'),
                'mc.station_code2' => DB::raw('mi.station_code2'),
                'mc.station_name2' => DB::raw('mi.station_name2'),
                'mc.rail_line_name2' => DB::raw('mi.rail_line_name2'),
                'mc.rail_line_name_moji2' => DB::raw('mi.rail_line_name_moji2'),
                'mc.station_code2' => DB::raw('mi.station_code2'),
                'mc.station_name2' => DB::raw('mi.station_name2'),
                'mc.work_or_bus2' => DB::raw('mi.work_or_bus2'),
                'mc.work_bus_time2' => DB::raw('mi.work_bus_time2'),
                'mc.travel_cost' => DB::raw('mi.travel_cost'),
                'mc.traffic_cost_type' => DB::raw('mi.traffic_cost_type'),
                'mc.month_traffic_cost' => DB::raw('mi.month_traffic_cost'),
                'mc.society_insurance' => DB::raw('mi.society_insurance'),
                'mc.sanatorium' => DB::raw('mi.sanatorium'),
                'mc.enterprise_pension' => DB::raw('mi.enterprise_pension'),
                'mc.wealth_shape' => DB::raw('mi.wealth_shape'),
                'mc.stock_option' => DB::raw('mi.stock_option'),
                'mc.retirement_pay' => DB::raw('mi.retirement_pay'),
                'mc.residence_pay' => DB::raw('mi.residence_pay'),
                'mc.family_pay' => DB::raw('mi.family_pay'),
                'mc.employee_dormitory' => DB::raw('mi.employee_dormitory'),
                'mc.company_house' => DB::raw('mi.company_house'),
                'mc.new_employee_training' => DB::raw('mi.new_employee_training'),
                'mc.overseas_training' => DB::raw('mi.overseas_training'),
                'mc.other_training' => DB::raw('mi.other_training'),
                'mc.welfare_program_remark' => DB::raw('mi.welfare_program_remark'),
                'mc.transfer' => DB::raw('mi.transfer'),
                'mc.business_contents' => DB::raw('mi.business_contents'),
                'mc.company_pr' => DB::raw('mi.company_pr'),
                'mc.business_other' => DB::raw('mi.business_other'),
                'mc.is_send' => DB::raw('mi.is_send'),
                'mc.publish_flg' => DB::raw('mi.publish_flg'),
                'mc.contact_mail_address' => DB::raw('mi.contact_mail_address'),
                'mc.contact_person_name' => DB::raw('mi.contact_mail_address'),
                'mc.contact_section_name' => DB::raw('mi.contact_section_name'),
                'mc.demand_prefecture' => DB::raw('mi.demand_prefecture'),
                'mc.demand_city__k' => DB::raw('mi.demand_city__k'),
                'mc.demand_city__f' => DB::raw('mi.demand_city__f'),
                'mc.demand_town' => DB::raw('mi.demand_town'),
                'mc.demand_address' => DB::raw('mi.demand_address'),
                'mc.demand_person_name' => DB::raw('mi.demand_person_name'),                
                'mc.demand_section_name' => DB::raw('mi.demand_section_name'),
                'mc.lis_person_code' => DB::raw('mi.lis_person_code'),
                'mc.lis_person_name' => DB::raw('mi.lis_person_name'),
                'mc.lis_mail_address' => DB::raw('mi.lis_mail_address'),
                'mc.group_code' => DB::raw('mi.group_code'),
                'mc.branch_code' => DB::raw('mi.branch_code'),
                'mc.new_job_mail' => DB::raw('mi.new_job_mail'),
                'mc.tanto1_yakusyoku' => DB::raw('mi.tanto1_yakusyoku'),
                'mc.tanto2_name ' => DB::raw('mi.tanto2_name'),
                'mc.tanto2_yakusyoku' => DB::raw('mi.tanto2_yakusyoku'),
                'mc.mailaddr' => DB::raw('mi.mailaddr'),
                'mc.keiyaku_ymd' => DB::raw('mi.keiyaku_ymd'),
                'mc.company_kbn ' => DB::raw('mi.company_kbn'),
                'mc.temp_permit_flag' => DB::raw('mi.temp_permit_flag'),
                'mc.intro_permit_flag' => DB::raw(' mi.intro_permit_flag'),
                'mc.simebi' => DB::raw('mi.simebi'),
                'mc.moshikomi_kbn ' => DB::raw('mi.moshikomi_kbn'),
                'mc.moshikomi_kbn_t' => DB::raw('mi.moshikomi_kbn_t'),
                'mc.new_person_mail' => DB::raw('mi.new_person_mail'),
                'mc.kado_kbn' => DB::raw('mi.kado_kbn'),
                'mc.kyujin_riyo_kbn' => DB::raw('mi.kyujin_riyo_kbn'),
                'mc.company_syudan1_1' => DB::raw('mi.company_syudan1_1'),
                'mc.company_syudan1_2' => DB::raw('mi.company_syudan1_2'),
                'mc.company_syudan2_1' => DB::raw('mi.company_syudan2_1'),
                'mc.company_syudan2_2' => DB::raw('mi.company_syudan2_2'),
                'mc.kyujin_yuryo_flg' => DB::raw('mi.kyujin_yuryo_flg'),
                'mc.keisai_riyo_flg' => DB::raw('mi.keisai_riyo_flg'),
                'mc.lis_regist_day' => DB::raw('mi.lis_regist_day'),
                'mc.sales_db_c_pass_flg' => DB::raw('mi.sales_db_c_pass_flg'),
                'mc.intbase_contract_day' => DB::raw('mi.intbase_contract_day'),
                'mc.sales_constitution_ratio' => DB::raw('mi.sales_constitution_ratio'),
                'mc.main_client' => DB::raw('mi.main_client'),
                'mc.competitor' => DB::raw('mi.competitor'),
                'mc.accounting_period1' => DB::raw('mi.accounting_period1'),
                'mc.sales_ammount1' => DB::raw('mi.sales_ammount1'),
                'mc.ordinary_profit1' => DB::raw('mi.ordinary_profit1'),
                'mc.accounting_period2' => DB::raw('mi.accounting_period2'),
                'mc.sales_amount2' => DB::raw('mi.sales_amount2'),                      'mc.ordinary_profit2' => DB::raw('mi.ordinary_profit2'),
                'mc.accounting_period3' => DB::raw('mi.accounting_period3'),
                'mc.sales_amount3' => DB::raw('mi.sales_amount3'),
                'mc.ordinary_profit3' => DB::raw('mi.ordinary_profit3'),
                'mc.important_notice' => DB::raw('mi.important_notice'),
                'mc.employee_average_age' => DB::raw('mi.employee_average_age'),
                'mc.main_stock_holder' => DB::raw('mi.main_stock_holder'),
                'mc.other ' => DB::raw('mi.other'),
                'mc.atmosphere' => DB::raw('mi.atmosphere'),
                ]);          
	    Log::info('master_company update 成功');
	    //dd(DB::getQueryLog());
	    Log::info(DB::getQueryLog());


            DB::commit();
		Storage::delete($file_path);//file Delete

        } catch (Exception $e) {
            DB::rollback();
            //エラー処理
	     Log::info('master_person insert Err: ' . $e);
            dd('Err: ' . $e);
        }
        return;
    } //end function
  //end importMaterCompany



} // end class
