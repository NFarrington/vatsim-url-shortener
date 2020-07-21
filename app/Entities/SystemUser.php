<?php

declare(strict_types=1);

namespace App\Entities;

use App\Exceptions\UnexpectedCallException;
use Doctrine\ORM\Mapping as ORM;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * @ORM\Entity
 * @ORM\Table(name="system_users")
 */
class SystemUser extends Entity implements Authenticatable
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
    protected string $username;

    /**
     * @ORM\Column(type="string")
     */
    protected string $password;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getAuthIdentifierName()
    {
        return 'id';
    }

    public function getAuthIdentifier()
    {
        return $this->id;
    }

    public function getAuthPassword()
    {
        return $this->password;
    }

    public function getRememberToken()
    {
        throw new UnexpectedCallException('Requested remember token for a system user.');
    }

    public function setRememberToken($value)
    {
        throw new UnexpectedCallException('Attempted to set remember token for a system user.');
    }

    public function getRememberTokenName()
    {
        throw new UnexpectedCallException('Requested remember token name for a system user.');
    }
}
