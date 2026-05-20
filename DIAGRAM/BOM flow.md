erDiagram
    production_boms ||--o{ production_bom_items : "has lines"
    production_boms }o--|| products : "output FG"
    production_bom_items }o--|| products : "component RM"
    production_orders }o--o| production_boms : "uses"
    production_orders ||--o{ production_order_bom_snapshot_items : "snapshot"
    production_orders ||--o{ production_batches : "runs"
    production_batches ||--o{ production_batch_consumptions : "consume"
    production_batches ||--o{ production_batch_outputs : "output"
