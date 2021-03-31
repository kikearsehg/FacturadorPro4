@extends('tenant.layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-8 col-md-12">
            <tenant-configurations-form :type-user="{{ json_encode(auth()->user()->type) }}"></tenant-configurations-form>
        </div>
        <div class="col-lg-4 col-md-12">
            <tenant-options-form></tenant-options-form>
        </div>
    </div>
@endsection
