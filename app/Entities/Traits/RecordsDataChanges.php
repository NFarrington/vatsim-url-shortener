<?php

declare(strict_types=1);

namespace App\Entities\Traits;

trait RecordsDataChanges
{
    public function getTrackedProperties(): array
    {
        return $this->trackedProperties ?? [];
    }
}
