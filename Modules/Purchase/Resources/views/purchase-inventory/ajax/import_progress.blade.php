@include('import.process-form', [
    'headingTitle' => __('app.importExcel') . ' ' . __('purchase::app.menu.inventory'),
    'processRoute' => route('purchase-inventory.import.process'),
    'backRoute' => route('purchase-inventory.index'),
    'backButtonText' => __('purchase::app.menu.inventory'),
])
