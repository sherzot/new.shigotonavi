<?php

namespace App\Http\Controllers;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\JobSearchLogExport;

class LogExportController extends Controller
{
    public function export()
    {
        return Excel::download(new JobSearchLogExport, 'job_search_log.xlsx');
    }
}
