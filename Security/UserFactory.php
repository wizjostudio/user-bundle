<?php
namespace Wizjo\Bundle\UserBundle\Security;

use Wizjo\Bundle\UserBundle\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Wizjo\Bundle\UserBundle\User\UserManagerInterface;

class UserFactory implements UserProviderInterface
{
    /**
     * @var UserManagerInterface
     */
    protected $manager;

    /**
     * @param UserManagerInterface $manager
     */
    public function __construct(UserManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param string $username
     *
     * @return SecurityUser
     * @throws UsernameNotFoundException
     */
    public function loadUserByUsername($username): SecurityUser
    {
        try {
            $user = $this->manager->getUser($username);
        } catch (UserNotFoundException $e) {
            throw new UsernameNotFoundException(sprintf('No user found for "%s"', $username));
        }

        $class = $this->manager->getSecurityUserClass();
        return new $class($user);
    }

    /**
     * @param UserInterface $user
     *
     * @return UserInterface
     * @throws UnsupportedUserException|UsernameNotFoundException
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof SecurityUser) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return $class === $this->manager->getSecurityUserClass();
    }
}
