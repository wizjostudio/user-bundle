<?php
namespace Wizjo\Bundle\UserBundle\Security;

use Wizjo\Bundle\UserBundle\Entity\UserInterface;

class SecurityUser implements SecurityUserInterface
{
    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var array|\string[]
     */
    protected $roles;

    /**
     * @var bool
     */
    protected $active;

    /**
     * @param UserInterface $user
     */
    public function __construct(UserInterface $user)
    {
        $this->username = $user->getEmail();
        $this->password = $user->getPassword();
        $this->roles = $user->getRoles();
        $this->active = $user->isActive();
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->getUsername();
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    public function getSalt(): void
    {
    }

    public function eraseCredentials(): void
    {
    }

    /**
     * @return string
     */
    public function serialize(): string
    {
        return serialize([
            $this->username,
            $this->password,
            $this->roles,
            $this->active,
        ]);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized): void
    {
        [
            $this->username,
            $this->password,
            $this->roles,
            $this->active,
        ] = unserialize($serialized, [
            'allowed_classes' => [self::class]
        ]);
    }
}
