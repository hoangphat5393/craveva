@extends('layouts.app')

@section('content')
    <div class="content-wrapper">
        @include('pricing::client_pricing.ajax.edit')
    </div>
@endsection
