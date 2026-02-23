@extends('layouts.app')

@section('content')
    <div class="content-wrapper">
        @include('pricing::company_pricing.ajax.create')
    </div>
@endsection
