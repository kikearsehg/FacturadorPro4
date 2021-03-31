@extends('tenant.layouts.app')

@section('content')
    <tenant-order-notes-form :type-user="{{json_encode(Auth::user()->type)}}"></tenant-order-notes-form>
@endsection
