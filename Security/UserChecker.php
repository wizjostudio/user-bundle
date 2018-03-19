<?php
namespace Wizjo\Bundle\UserBundle\Security;

use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker extends \Symfony\Component\Security\Core\User\UserChecker
{
    /**
     * @param UserInterface $user
     */
    public function checkPreAuth(UserInterface $user)
    {
        parent::checkPreAuth($user);

        if (!$user instanceof SecurityUserInterface) {
            return;
        }

        if (!$user->isActive()) {
            $ex = new DisabledException('User account is disabled.');
            $ex->setUser($user);
            throw $ex;
        }
    }
}
