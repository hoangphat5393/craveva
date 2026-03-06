<?php

namespace Modules\Webhooks\Enums;

enum ProductVariable: string implements Variable
{
    case name = '##NAME##';
    case price = '##PRICE##';
    case description = '##DESCRIPTION##';
    case sku = '##SKU##';
    case taxes = '##TAXES##';
    case hsn_sac_code = '##HSN_SAC_CODE##';
    case purchase_price = '##PURCHASE_PRICE##';
    case allow_purchase = '##ALLOW_PURCHASE##';
    case status = '##STATUS##';
    case category = '##CATEGORY##';
    case sub_category = '##SUB_CATEGORY##';

    public function key(): string
    {
        return match ($this) {
            default => $this->name,
        };
    }

    public static function invalidVariables(): array
    {
        return [
            'id',
            'company_id',
            'last_updated_by',
            'added_by',
            'unit_id',
            'category_id',
            'sub_category_id',
            'company',
            'image',
            'default_image',
            'downloadable_file',
            'image_url',
            'download_file_url',
            'files',
            'files_count',
            'leads_count',
            'order_item_count',
            'inventory_count',
            'tax',
            'category',
            'subCategory',
        ];
    }
}
