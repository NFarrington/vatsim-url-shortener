<?php

declare(strict_types=1);

namespace App\Entities;

use App\Entities\Traits\RecordsCreatedAt;
use App\Entities\Traits\RecordsUpdatedAt;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="news")
 */
class News extends Entity
{
    use RecordsCreatedAt, RecordsUpdatedAt;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", options={"unsigned": true})
     */
    protected int $id;

    /**
     * @ORM\Column(type="string", length=250)
     */
    protected string $title;

    /**
     * @ORM\Column(type="text")
     */
    protected string $content;

    /**
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    protected bool $published = false;

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): void
    {
        $this->published = $published;
    }
}
