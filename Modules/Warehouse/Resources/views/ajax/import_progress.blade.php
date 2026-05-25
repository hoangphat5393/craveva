@include('import.process-form', [
    'headingTitle' => __('warehouse::app.importWarehouses'),
    'processRoute' => route('warehouse.import.process'),
    'backRoute' => route('warehouse.index'),
    'backButtonText' => __('warehouse::app.warehouses'),
])
