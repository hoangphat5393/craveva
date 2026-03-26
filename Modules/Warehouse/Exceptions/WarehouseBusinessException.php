<?php

namespace Modules\Warehouse\Exceptions;

use RuntimeException;
use Throwable;

/**
 * User-facing warehouse domain error (stock, transfer, company scope).
 * Message must already be translated at throw site.
 */
class WarehouseBusinessException extends RuntimeException
{
    /**
     * @param  array<string, mixed>  $logContext
     */
    public function __construct(
        string $userMessage,
        private array $logContext = [],
        ?Throwable $previous = null
    ) {
        parent::__construct($userMessage, 0, $previous);
    }

    public function getUserMessage(): string
    {
        return $this->getMessage();
    }

    /**
     * @return array<string, mixed>
     */
    public function getLogContext(): array
    {
        return $this->logContext;
    }
}
