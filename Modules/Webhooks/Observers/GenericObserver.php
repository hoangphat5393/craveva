<?php

namespace Modules\Webhooks\Observers;

use Illuminate\Database\Eloquent\Model;
use Modules\Webhooks\Jobs\SendWebhook;

class GenericObserver
{
    public function created(Model $model)
    {
        $modelName = class_basename($model);
        
        if (!in_array($modelName, \Modules\Webhooks\Entities\WebhooksSetting::WEBHOOK_FOR)) {
            return;
        }

        SendWebhook::dispatch($model->toArray(), $modelName, $model->company_id)
            ->delay(5)
            ->onQueue('default');
    }
}
