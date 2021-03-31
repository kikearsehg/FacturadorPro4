<?php

namespace Modules\Account\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Company;
use App\Models\Tenant\Document;
use App\Models\Tenant\Purchase;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\Account\Exports\ReportFormatPurchaseExport;
use Modules\Account\Exports\ReportFormatSaleExport;

class FormatController extends Controller
{
    public function index()
    {
        return view('account::account.format');
    }

    public function download(Request $request)
    {
        $type = $request->input('type');
        $month = $request->input('month');
        $d_start = Carbon::parse($month.'-01')->format('Y-m-d');
        $d_end = Carbon::parse($month.'-01')->endOfMonth()->format('Y-m-d');

        if($type === 'sale') {
            $filename = 'Reporte_Formato_Ventas_'.date('YmdHis');
            $data = [
                'period' => $month,
                'company' => $this->getCompany(),
                'records' => $this->getSaleDocuments($d_start, $d_end)
            ];

            return (new ReportFormatSaleExport())
                ->data($data)
                ->download($filename.'.xlsx');
        } else {
            $filename = 'Reporte_Formato_Compras_'.date('YmdHis');
            $data = [
                'period' => $month,
                'company' => $this->getCompany(),
                'records' => $this->getPurchaseDocuments($d_start, $d_end)
            ];

            return (new ReportFormatPurchaseExport())
                ->data($data)
                ->download($filename.'.xlsx');
        }
    }

    private function getCompany()
    {
        $company = Company::query()->first();

        return [
            'name' => $company->name,
            'number' => $company->number,
        ];
    }

    private function getSaleDocuments($d_start, $d_end)
    {
        return Document::query()
                                ->whereBetween('date_of_issue', [$d_start, $d_end])
                                // ->whereIn('document_type_id', ['01', '03'])
                                ->whereIn('currency_type_id', ['PEN', 'USD'])
                                ->orderBy('series')
                                ->orderBy('number')
                                ->get()->transform(function($row) {
                                    $total = $row->total;
                                    $total_taxed = $row->total_taxed;
                                    $symbol = $row->currency_type->symbol;
                                    $total_igv = $row->total_igv;

                                    if($row->currency_type_id == 'USD')
                                    {
                                        $total = round($row->total * $row->exchange_rate_sale, 2);
                                        $total_taxed = round($row->total_taxed * $row->exchange_rate_sale, 2);
                                        $symbol = 'S/';

                                        $total_igv = round($row->total_igv * $row->exchange_rate_sale, 2);
                                    }


                                    return [
                                        'date_of_issue' => $row->date_of_issue->format('d/m/Y'),
                                        'document_type_id' => $row->document_type_id,
                                        'state_type_id' => $row->state_type_id,
                                        'series' => $row->series,
                                        'number' => $row->number,
                                        'customer_identity_document_type_id' => $row->customer->identity_document_type_id,
                                        'customer_number' => $row->customer->number,
                                        'customer_name' => $row->customer->name,
                                        'total_exportation' => $row->total_exportation,
                                        'total_taxed' => $total_taxed,
                                        'total_exonerated' => $row->total_exonerated,
                                        'total_unaffected' => $row->total_unaffected,
                                        'total_plastic_bag_taxes' => $row->total_plastic_bag_taxes,
                                        'total_isc' => $row->total_isc,
                                        'total_igv' => $total_igv,
                                        'total' => $total,
                                        'exchange_rate_sale' => $row->exchange_rate_sale,
                                        'currency_type_symbol' => $symbol,
                                        'affected_document' => (in_array($row->document_type_id, ['07', '08'])) ? [

                                            'date_of_issue' => $row->note->affected_document->date_of_issue->format('d/m/Y'),
                                            'document_type_id' => $row->note->affected_document->document_type_id,
                                            'series' => $row->note->affected_document->series,
                                            'number' => $row->note->affected_document->number,

                                        ] : null
                                    ];
            });

    }

    private function getPurchaseDocuments($d_start, $d_end)
    {
        return Purchase::query()
            ->whereBetween('date_of_issue', [$d_start, $d_end])
            ->whereIn('document_type_id', ['01', '03', '14'])
            ->whereIn('currency_type_id', ['PEN'])
            ->orderBy('series')
            ->orderBy('number')
            ->get()->transform(function($row) {
                return [
                    'date_of_issue' => $row->date_of_issue->format('d/m/Y'),
                    'date_of_due' => $row->date_of_due->format('d/m/Y'),
                    'state_type_id' => $row->state_type_id,
                    'document_type_id' => $row->document_type_id,
                    'series' => $row->series,
                    'number' => $row->number,
                    'supplier_identity_document_type_id' => $row->supplier->identity_document_type_id,
                    'supplier_number' => $row->supplier->number,
                    'supplier_name' => $row->supplier->name,
                    'total_exportation' => $row->total_exportation,
                    'total_taxed' => $row->total_taxed,
                    'total_exonerated' => $row->total_exonerated,
                    'total_unaffected' => $row->total_unaffected,
                    'total_isc' => $row->total_isc,
                    'total_igv' => $row->total_igv,
                    'total' => $row->total,
                    'exchange_rate_sale' => $row->exchange_rate_sale,
                    'currency_type_symbol' => $row->currency_type->symbol
                ];
            });

    }
}
