<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Series;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Traits\OfflineTrait;

class ReportConsistencyDocumentController extends Controller
{

    use OfflineTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        return view('tenant.reports.consistency-documents.index');
    }
    
    /**
     * Lists
     * @return \Illuminate\Http\Response
     */
    public function lists() {
        return Series::query()
            ->select('number')
            ->with(['documents' => function($queryDocuments) {
                if (!$this->getIsClient()) $queryDocuments->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()]);
                
                $queryDocuments->select('series', 'number', 'state_type_id');
            }])
            ->get()
            ->map(function($serie) {
                $start = $serie->documents->min('number') ?? 0;
                $end = $serie->documents->max('number') ?? 0;
                $numbers = (count($serie->documents) > 0) ? $serie->documents->pluck('number')->toArray() : [0];
                $registered = (count($serie->documents) > 0) ? $serie->documents()->where('state_type_id', '01')->pluck('number')->toArray() : [];
                $sent = (count($serie->documents) > 0) ? $serie->documents()->where('state_type_id', '03')->pluck('number')->toArray() : [];
                $accepted = (count($serie->documents) > 0) ? $serie->documents()->where('state_type_id', '05')->pluck('number')->toArray() : [];
                $observed = (count($serie->documents) > 0) ? $serie->documents()->where('state_type_id', '07')->pluck('number')->toArray() : [];
                $rejected = (count($serie->documents) > 0) ? $serie->documents()->where('state_type_id', '09')->pluck('number')->toArray() : [];
                $canceled = (count($serie->documents) > 0) ? $serie->documents()->where('state_type_id', '11')->pluck('number')->toArray() : [];
                $canceled = (count($serie->documents) > 0) ? $serie->documents()->where('state_type_id', '11')->pluck('number')->toArray() : [];
                $byVoiding = (count($serie->documents) > 0) ? $serie->documents()->where('state_type_id', '13')->pluck('number')->toArray() : [];
                
                return [
                    'serie' => $serie,
                    'start' => $start,
                    'end' => $end,
                    'diff' => join(', ', array_diff(range($start, $end), $numbers)),
                    'registered' => join(', ', $registered),
                    'sent' => join(', ', $sent),
                    'accepted' => join(', ', $accepted),
                    'observed' => join(', ', $observed),
                    'rejected' => join(', ', $rejected),
                    'canceled' => join(', ', $canceled),
                    'byVoiding' => join(', ', $byVoiding)
                ];
            });
    }
}
