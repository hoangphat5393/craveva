@include('import.process-form', [
    'headingTitle' => __('app.importExcel') . ' ' . __('app.menu.product'),
    'processRoute' => route('purchase-products.import.process'),
    'backRoute' => route('purchase-products.index'),
    'backButtonText' => __('app.backToProducts'),
    'unitTypes' => $unitTypes ?? collect(),
])
