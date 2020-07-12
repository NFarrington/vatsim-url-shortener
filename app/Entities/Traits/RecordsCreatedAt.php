<?php

declare(strict_types=1);

namespace App\Entities\Traits;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Illuminate\Support\Carbon;

trait RecordsCreatedAt
{
    /**
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     * @Gedmo\Timestampable(on="create")
     */
    protected DateTime $createdAt;

    public function getCreatedAt(): Carbon
    {
        return Carbon::instance($this->createdAt);
    }

    public function setCreatedAt(DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}
