@extends('layouts.app')

@section('content')

    <div class="project-wrapper border-top-0 p-20">
        @include($view)
    </div>

@endsection

@push('scripts')
    <script>
        $("body").on("click", ".project-menu .ajax-tab", function (event) {
            event.preventDefault();

            $('.project-menu .p-sub-menu').removeClass('active');
            $(this).addClass('active');

            const requestUrl = this.href;

            window.apiHttp.get(requestUrl)
                .then(function (response) {
                    if (response.status == "success") {
                        window.history.pushState({}, '', requestUrl);
                        $('.content-wrapper').html(response.html);
                        init('.content-wrapper');
                    }
                })
                .catch(function (err) {
                    $.handleApiFormError(err);
                });
        });
    </script>
    <script>
        const activeTab = "{{ $activeTab }}";
        $('.project-menu .' + activeTab).addClass('active');

    </script>
@endpush
