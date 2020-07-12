<?php

declare(strict_types=1);

namespace App\Entities;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="email_events")
 */
class EmailEvent extends Entity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", options={"unsigned": true})
     */
    protected int $id;

    /**
     * @ORM\Column(type="string")
     */
    protected string $broker;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected string $messageId;

    /**
     * @ORM\Column(type="string")
     */
    protected string $name;

    /**
     * @ORM\Column(type="string")
     */
    protected string $recipient;

    /**
     * @ORM\Column(type="json")
     * @var mixed
     */
    protected $data;

    /**
     * @ORM\Column(type="datetime")
     */
    protected DateTime $triggeredAt;

    public function getId(): int
    {
        return $this->id;
    }

    public function setBroker(string $broker): void
    {
        $this->broker = $broker;
    }

    public function setMessageId(string $messageId): void
    {
        $this->messageId = $messageId;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setRecipient(string $recipient): void
    {
        $this->recipient = $recipient;
    }

    public function setData($data): void
    {
        $this->data = $data;
    }

    public function setTriggeredAt(DateTime $triggeredAt): void
    {
        $this->triggeredAt = $triggeredAt;
    }
}
