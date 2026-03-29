{{-- Full jQuery UI bundle overwrites $.fn.tooltip (Bootstrap 4). Restore Bootstrap and re-delegate body tooltips. --}}
<script>
    (function($) {
        if (typeof window.bootstrapTooltipFn === 'undefined') {
            return;
        }
        $.fn.tooltip = window.bootstrapTooltipFn;
        try {
            $('body').tooltip('dispose');
        } catch (e) {
            /* may be jQuery UI instance or not initialized */ }
        $('body').tooltip({
            selector: '[data-toggle="tooltip"]',
            trigger: 'hover',
        });
    })(jQuery);
</script>
