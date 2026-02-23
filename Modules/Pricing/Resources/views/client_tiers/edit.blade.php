@extends('layouts.app')

@section('content')
    <div class="content-wrapper">
        @include('pricing::client_tiers.ajax.edit')
    </div>
@endsection
