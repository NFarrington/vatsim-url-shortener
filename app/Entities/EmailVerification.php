<?php

declare(strict_types=1);

namespace App\Entities;

use App\Entities\Traits\RecordsCreatedAt;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'email_verifications')]
#[ORM\Entity]
class EmailVerification extends Entity
{
    use RecordsCreatedAt;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    protected int $id;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'emailVerifications')]
    protected User $user;

    #[ORM\Column(type: 'string')]
    protected string $token;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }
}
