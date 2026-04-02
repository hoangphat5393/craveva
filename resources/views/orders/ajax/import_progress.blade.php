@include('import.process-form', [
    'headingTitle' => __('app.importExcel') . ' ' . __('app.menu.orders'),
    'processRoute' => route('orders.import.process'),
    'backRoute' => route('orders.index'),
    'backButtonText' => __('app.back'),
])
