@include('import.process-form', [
    'headingTitle' => __('app.importExcel') . ' ' . __('app.menu.salesHistory'),
    'processRoute' => route('sales-history.import.process'),
    'backRoute' => route('sales-history.index'),
    'backButtonText' => __('app.back'),
])
