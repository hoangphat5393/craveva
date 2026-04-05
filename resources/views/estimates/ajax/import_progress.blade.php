@include('import.process-form', [
    'headingTitle' => __('app.importExcel') . ' ' . __('app.quotation_ui.singular'),
    'processRoute' => route('estimates.import.process'),
    'backRoute' => route('estimates.index'),
    'backButtonText' => __('app.backToQuotationList'),
])
