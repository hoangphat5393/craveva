@include('import.process-form', [
    'headingTitle' => __('app.importExcel') . ' ' . __('warehouse::app.warehouse'),
    'processRoute' => route('warehouse.import.process'),
    'backRoute' => route('warehouse.index'),
    'backButtonText' => __('warehouse::app.warehouses'),
])
