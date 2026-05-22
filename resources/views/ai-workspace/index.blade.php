@extends('layouts.app')

@section('content')
    <div class="content-wrapper ai-workspace-page">
        <div id="ai-workspace-page-root" class="ai-workspace-page__root"></div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            const agentId = @json($agentId);
            const widgetScriptUrl = @json($widgetScriptUrl);
            const apiKey = @json($apiKey);
            const root = document.getElementById('ai-workspace-page-root');

            if (!widgetScriptUrl || !agentId) {
                return;
            }

            const existingScript = document.querySelector('script[data-agent-id="' + agentId + '"][data-ai-workspace-page]');
            if (existingScript) {
                return;
            }

            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType !== 1 || node.parentNode !== document.body) {
                            return;
                        }

                        if (root && !root.contains(node)) {
                            root.appendChild(node);
                        }
                    });
                });
            });

            observer.observe(document.body, {
                childList: true
            });

            const script = document.createElement('script');
            script.src = widgetScriptUrl;
            script.async = true;
            script.crossOrigin = 'anonymous';
            script.setAttribute('data-agent-id', agentId);
            script.setAttribute('data-ai-workspace-page', '1');

            if (apiKey) {
                script.setAttribute('data-api-key', apiKey);
            }

            script.onload = function() {
                setTimeout(function() {
                    observer.disconnect();
                }, 5000);
            };

            document.body.appendChild(script);
        })();
    </script>
@endpush
