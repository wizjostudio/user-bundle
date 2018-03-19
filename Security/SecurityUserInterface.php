<?php
namespace Wizjo\Bundle\UserBundle\Security;

use Symfony\Component\Security\Core\User\UserInterface;

interface SecurityUserInterface extends UserInterface, \Serializable
{
    /**
     * @return bool
     */
    public function isActive(): bool;
}
