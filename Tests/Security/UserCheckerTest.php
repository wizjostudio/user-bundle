<?php
namespace Wizjo\Bundle\UserBundle\Tests\Security;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\User\UserInterface;
use Wizjo\Bundle\UserBundle\Security\SecurityUserInterface;
use Wizjo\Bundle\UserBundle\Security\UserChecker;

class UserCheckerTest extends TestCase
{
    public function testCheckPreAuth()
    {
        $user = $this->createMock(SecurityUserInterface::class);

        $user
            ->expects(static::once())
            ->method('isActive')
            ->willReturn(true);

        $checker = new UserChecker();
        $checker->checkPreAuth($user);
    }

    public function testCheckPreAuthInactiveUser()
    {
        $user = $this->createMock(SecurityUserInterface::class);

        $user
            ->expects(static::once())
            ->method('isActive')
            ->willReturn(false);

        $this->expectException(DisabledException::class);

        $checker = new UserChecker();
        $checker->checkPreAuth($user);
    }

    public function testCheckPreAuthUserNotInstanceOfSecurityUserInterface()
    {
        $user = $this->createMock(TestUserInterface::class);

        $user
            ->expects(static::never())
            ->method('isActive');

        $checker = new UserChecker();
        $checker->checkPreAuth($user);
    }
}

interface TestUserInterface extends UserInterface
{
    public function isActive(): bool;
}
