@extends('layouts.app')

@section('content')
    <div class="content-wrapper">
        @include('pricing::tiers.ajax.create')
    </div>
@endsection
