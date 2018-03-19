<?php
namespace Wizjo\Bundle\UserBundle\Tests\User;

use PHPUnit\Framework\TestCase;
use Wizjo\Bundle\UserBundle\Exception\UserManagerNotFoundException;
use Wizjo\Bundle\UserBundle\User\UserManagerInterface;
use Wizjo\Bundle\UserBundle\User\UserManagerRegistry;
use Wizjo\Bundle\UserBundle\User\UserManagerRegistryInterface;

class UserManagerRegistryTest extends TestCase
{
    public function testIsInstanceOfUserManagerRegistryInterface()
    {
        $registry = new UserManagerRegistry();

        static::assertInstanceOf(UserManagerRegistryInterface::class, $registry);
    }

    public function testAddAndGetManager()
    {
        $class = \stdClass::class;
        $name = 'test';

        $manager = $this->createMock(UserManagerInterface::class);
        $manager
            ->expects(static::once())
            ->method('getUserEntityClass')
            ->willReturn($class);

        $registry = new UserManagerRegistry();

        $registry->addManager($manager, $name);

        static::assertEquals($manager, $registry->getManagerForClass($class));
        static::assertEquals($manager, $registry->getManager($name));
    }

    public function testGetManagerNotFound()
    {
        $registry = new UserManagerRegistry();

        $this->expectException(UserManagerNotFoundException::class);
        $registry->getManager('test');
    }

    public function testGetManagerForClassNotFound()
    {
        $registry = new UserManagerRegistry();

        $this->expectException(UserManagerNotFoundException::class);
        $registry->getManagerForClass(\stdClass::class);
    }
}
