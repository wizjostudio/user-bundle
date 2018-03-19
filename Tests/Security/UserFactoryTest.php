<?php
namespace Wizjo\Bundle\UserBundle\Tests\Security;

use PHPUnit\Framework\TestCase;
use Wizjo\Bundle\UserBundle\Entity\UserInterface;
use Wizjo\Bundle\UserBundle\Exception\UserNotFoundException;
use Wizjo\Bundle\UserBundle\Security\SecurityUser;
use Wizjo\Bundle\UserBundle\Security\SecurityUserInterface;
use Wizjo\Bundle\UserBundle\Security\UserFactory;
use Wizjo\Bundle\UserBundle\User\UserManager;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class UserFactoryTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|UserManager
     */
    private $manager;

    public function setUp()
    {
        $this->manager = $this->createMock(UserManager::class);
    }

    public function tearDown()
    {
        $this->manager = null;
    }

    public function testLoadUserByUsername()
    {
        $this->manager
            ->method('getSecurityUserClass')
            ->willReturn(SecurityUser::class);

        $user = $this->createMock(UserInterface::class);
        $this->manager
            ->expects(static::once())
            ->method('getUser')
            ->willReturn($user);

        $factory = new UserFactory($this->manager);
        $result = $factory->loadUserByUsername('');

        static::assertInstanceOf(SecurityUserInterface::class, $result);
    }

    public function testLoadUserByUsernameNotFound()
    {
        $this->manager
            ->expects(static::once())
            ->method('getUser')
            ->willThrowException(new UserNotFoundException());

        $this->expectException(UsernameNotFoundException::class);
        $factory = new UserFactory($this->manager);
        $factory->loadUserByUsername('');
    }

    public function testRefreshUser()
    {
        $email = 'test@example.com';

        $user = $this->createMock(UserInterface::class);
        $user
            ->method('getEmail')
            ->willReturn($email);

        $this->manager
            ->expects(static::once())
            ->method('getUser')
            ->with($email)
            ->willReturn($user);

        $this->manager
            ->method('getSecurityUserClass')
            ->willReturn(SecurityUser::class);

        $securityUser = new SecurityUser($user);

        $factory = new UserFactory($this->manager);
        $result = $factory->refreshUser($securityUser);

        static::assertEquals($securityUser, $result);
    }

    public function testRefreshUserInvalidObject()
    {
        $securityUser = new class implements \Symfony\Component\Security\Core\User\UserInterface {
            public function eraseCredentials()
            {
            }

            public function getPassword()
            {
            }

            public function getRoles()
            {
            }

            public function getSalt()
            {
            }

            public function getUsername()
            {
            }
        };

        $factory = new UserFactory($this->manager);

        $this->expectException(UnsupportedUserException::class);
        $factory->refreshUser($securityUser);
    }

    /**
     * @param $class
     * @param $expected
     *
     * @dataProvider supportsClassProvider
     */
    public function testSupportsClass($class, $expected)
    {
        $this->manager
            ->expects(static::once())
            ->method('getSecurityUserClass')
            ->willReturn(SecurityUser::class);

        $factory = new UserFactory($this->manager);
        $result = $factory->supportsClass($class);

        static::assertEquals($expected, $result);
    }

    public function supportsClassProvider()
    {
        return [
            [SecurityUser::class, true],
            [\stdClass::class, false]
        ];
    }

}

