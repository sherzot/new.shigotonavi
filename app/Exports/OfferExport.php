<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;
//use App\Models\PersonOffer;
use Illuminate\Support\Facades\DB;

//class OfferExport implements FromCollection
class OfferExport implements FromViewi
{
    public function view(): View
    {
        $offers = DB::table('person_offer')
                ->select('staff_code', 'order_code', 'company_code', 'agent_code', 'offer_flag', 'created_at', 'update_at')
                ->get();

        return view('exports.offer', compact('offers') );

        //return view('exports.offer', [
        //    'offer' => PersonOffer::all()
        //]);
    }


    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        //
    }
}
