<?php

namespace Modules\Purchase\Observers;

use Modules\Purchase\Entities\PurchaseOrderFile;

class PurchaseOrderFileObserver
{
    public function saving(PurchaseOrderFile $file)
    {
        if (! isRunningInConsoleOrSeeding()) {

            if (user()) {
                $file->last_updated_by = user()->id;
            }
        }
    }

    public function creating(PurchaseOrderFile $file)
    {
        $file->added_by = user() ? user()->id : null;
    }
}
