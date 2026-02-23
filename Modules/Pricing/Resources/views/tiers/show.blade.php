@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('filter-section')
    <!-- PRICING HEADER START -->
    <div class="d-flex filter-box project-header bg-white">
        <div class="mobile-close-overlay w-100 h-100" id="close-client-overlay"></div>
        <div class="project-menu d-lg-flex" id="mob-client-detail">
            <a class="d-none close-it" href="javascript:;" id="close-client-detail">
                <i class="fa fa-times"></i>
            </a>

            <x-tab :href="route('pricing.tiers.show', $pricingTier->id)" :text="__('modules.projects.overview')" class="overview" />
        </div>
    </div>
    <!-- PRICING HEADER END -->
@endsection

@section('content')
    <div class="content-wrapper border-top-0 client-detail-wrapper">
        @include('pricing::tiers.ajax.show')
    </div>
@endsection

@push('scripts')
    <script>
        $("body").on("click", ".project-menu .ajax-tab", function(event) {
            event.preventDefault();

            $('.project-menu .p-sub-menu').removeClass('active');
            $(this).addClass('active');

            const requestUrl = this.href;

            $.easyAjax({
                url: requestUrl,
                blockUI: true,
                container: ".content-wrapper",
                historyPush: true,
                success: function(response) {
                    if (response.status == "success") {
                        $('.content-wrapper').html(response.html);
                    } else {
                        $('.content-wrapper').html(response);
                    }
                    init('.content-wrapper');
                }
            });
        });

        const activeTab = "overview";
        $('.project-menu .' + activeTab).addClass('active');
    </script>
@endpush
