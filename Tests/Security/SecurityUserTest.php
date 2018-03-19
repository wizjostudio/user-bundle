<?php
namespace Wizjo\Bundle\UserBundle\Tests\Security;

use PHPUnit\Framework\TestCase;
use Wizjo\Bundle\UserBundle\Entity\UserInterface;
use Wizjo\Bundle\UserBundle\Security\SecurityUser;

class SecurityUserTest extends TestCase
{
    private $email = 'test@example.com';
    private $password = 'secret';
    private $roles = ['ROLE_ADMIN'];

    public function testConstructor()
    {
        $email = 'test@example.com';
        $password = 'secret';
        $roles = ['ROLE_ADMIN'];
        $active = true;

        $user = $this->getUserMock($email, $password, $roles, $active);
        $securityUser = new SecurityUser($user);

        static::assertEquals($email, $securityUser->getEmail());
        static::assertEquals($password, $securityUser->getPassword());
        static::assertEquals($roles, $securityUser->getRoles());
        static::assertEquals($active, $securityUser->isActive());
    }

    public function testSerialize()
    {
        $user = $this->getUserMock();
        $securityUser = new SecurityUser($user);

        $resultSerialized = serialize($securityUser);
        $resultUnserialized = unserialize($resultSerialized);

        static::assertEquals($securityUser, $resultUnserialized);
    }

    public function testDummyMethods()
    {
        $user = $this->getUserMock();
        $securityUser = new SecurityUser($user);

        static::assertNull($securityUser->getSalt());
        static::assertNull($securityUser->eraseCredentials());
    }

    private function getUserMock($email = null, $password = null, array $roles = [], $isActive = true)
    {
        $user = $this->createMock(UserInterface::class);

        $user
            ->expects(static::once())
            ->method('getEmail')
            ->willReturn($email ?: $this->email);

        $user
            ->expects(static::once())
            ->method('getPassword')
            ->willReturn($password ?: $this->password);

        $user
            ->expects(static::once())
            ->method('getRoles')
            ->willReturn($roles ?: $this->roles);

        $user
            ->expects(static::once())
            ->method('isActive')
            ->willReturn((bool) $isActive);

        return $user;
    }
}
