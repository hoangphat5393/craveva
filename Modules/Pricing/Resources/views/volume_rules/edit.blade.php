@extends('layouts.app')

@section('content')
    <div class="content-wrapper">
        @include('pricing::volume_rules.ajax.edit')
    </div>
@endsection

