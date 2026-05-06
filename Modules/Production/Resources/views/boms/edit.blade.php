@extends('layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="d-flex justify-content-between action-bar flex-wrap border-bottom-grey pb-2 mb-3">
            <div class="align-items-center mt-3">
                <x-forms.link-secondary :link="route('production.boms.show', $bom)" class="float-left" icon="arrow-left">
                    @lang('app.back')
                </x-forms.link-secondary>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 pl-3">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-10">
                <form method="post" action="{{ route('production.boms.update', $bom) }}" class="bg-white rounded p-4">
                    @csrf
                    @method('PUT')
                    @include('production::boms.partials.form', ['bom' => $bom])
                    <div class="w-100 border-top-grey pt-3 mt-3 d-flex flex-wrap">
                        <button type="submit" class="btn btn-primary rounded f-14 p-2 mr-3">
                            <i class="fa fa-check mr-1"></i>@lang('app.save')
                        </button>
                        <x-forms.button-cancel :link="route('production.boms.show', $bom)" class="border-0">@lang('app.cancel')</x-forms.button-cancel>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
