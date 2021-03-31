<?php

namespace Modules\Pos\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Models\Tenant\Cash;
use App\Models\Tenant\Company;
use App\Models\Tenant\PaymentMethodType;
use Mpdf\HTMLParserMode;
use Mpdf\Mpdf;
use Modules\Pos\Exports\ReportCashExport;
use Modules\Pos\Mail\CashEmail;
use Illuminate\Support\Facades\Mail;


class CashController extends Controller
{

    public function email(Request $request)
    {

        $request->validate(
            ['email' => 'required']
        );
        
        $company = Company::active();
        $email = $request->input('email');

        Mail::to($email)->send(new CashEmail($company, $this->getPdf($request->cash_id)));

        return [
            'success' => true
        ];
    }


    private function getPdf($cash){

        $cash = Cash::findOrFail($cash);
        $company = Company::first();

        $methods_payment = collect(PaymentMethodType::all())->transform(function($row){
            return (object)[
                'id' => $row->id,
                'name' => $row->description,
                'sum' => 0
            ];
        });

        set_time_limit(0);
        
        $quantity_rows = $cash->cash_documents()->count();

        $html = view('pos::cash.report_pdf_ticket', compact("cash", "company", "methods_payment"))->render();

        $width = 78;

        $pdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => [
                $width,
                190 +
                ($quantity_rows * 8) 
            ],
            'margin_top' => 5,
            'margin_right' => 5,
            'margin_bottom' => 5,
            'margin_left' => 5
        ]);

        $pdf->WriteHTML($html);

        return $pdf->output('', 'S');
    }

    public function reportTicket($cash) {

        $temp = tempnam(sys_get_temp_dir(), 'cash_pdf_ticket');
        file_put_contents($temp, $this->getPdf($cash));

        return response()->file($temp);

    }


    public function reportExcel($cash) {

        $cash = Cash::findOrFail($cash);
        $company = Company::first();

        $methods_payment = collect(PaymentMethodType::all())->transform(function($row){
            return (object)[
                'id' => $row->id,
                'name' => $row->description,
                'sum' => 0
            ];
        });

        set_time_limit(0);
        $filename = "Reporte_POS - {$cash->user->name} - {$cash->date_opening} {$cash->time_opening}";
        
        return (new ReportCashExport)
                ->cash($cash)
                ->company($company)
                ->methods_payment($methods_payment)
                ->download($filename.'.xlsx');

    }


}
