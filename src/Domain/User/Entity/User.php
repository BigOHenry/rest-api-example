<?php

declare(strict_types=1);

namespace App\Domain\User\Entity;

use App\Domain\User\ValueObject\UserRole;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
#[ORM\Table(name: 'appuser')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id;

    #[ORM\Column(name: 'email', type: 'string', length: 255, unique: true)]
    private string $email;

    #[ORM\Column(type: 'string', length: 200, nullable: false)]
    private string $password;

    #[ORM\Column(type: 'string', length: 200, nullable: false)]
    private string $name;

    #[ORM\Column(type: 'string', nullable: false, enumType: UserRole::class)]
    private UserRole $role;

    protected function __construct(?int $id = null)
    {
        $this->id = $id;
    }

    public static function create(string $email, string $password, string $name, UserRole $role = UserRole::READER): self
    {
        $user = new self();
        $user->setEmail($email);
        $user->setPassword($password);
        $user->setName($name);
        $user->setRole($role);

        return $user;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setRole(UserRole $role): void
    {
        $this->role = $role;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRole(): UserRole
    {
        return $this->role;
    }

    public function getRoles(): array
    {
        return ['ROLE_' . mb_strtoupper($this->role->value)];
    }

    public function eraseCredentials(): void
    {
        // nothing to erase
    }

    public function getUserIdentifier(): string
    {
        \assert($this->email !== '', 'Email should never be empty');

        return $this->email;
    }

    protected function setId(?int $id): void
    {
        $this->id = $id;
    }
}
