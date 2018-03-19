<?php
namespace Wizjo\Bundle\UserBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Wizjo\Bundle\UserBundle\Entity\User;
use Wizjo\Bundle\UserBundle\Entity\UserInterface;

class UserTest extends TestCase
{
    public function testImplementsUserInterface()
    {
        $user = $this->getMockForAbstractClass(User::class, [], '', false);
        static::assertInstanceOf(UserInterface::class, $user);
    }

    public function testConstructor()
    {
        $email = 'test@example.com';
        $name = 'John Doe';
        $roles = [];

        $user = $this->getMockForAbstractClass(
            User::class,
            [
                $email,
                $name
            ]
        );

        static::assertEquals($email, $user->getEmail());
        static::assertEquals($name, $user->getName());
        static::assertEquals($roles, $user->getRoles());
        static::assertEquals('', $user->getPassword());
        static::assertTrue($user->isActive());
    }

    /**
     * @dataProvider setterAndGetterProvider
     */
    public function testSetterAndGetter($value, $setMethod, $getMethod)
    {
        $user = $this->getMockForAbstractClass(User::class, ['','']);

        $user->{$setMethod}($value);

        $result = $user->{$getMethod}();
        static::assertEquals($value, $result);
    }

    /**
     * @return array
     */
    public function setterAndGetterProvider()
    {
        return [
            ['test', 'setName', 'getName'],
            ['test', 'setPassword', 'getPassword'],
            ['test@example.com', 'setEmail', 'getEmail'],
            [['ROLE_TEST'], 'setRoles', 'getRoles'],
            [false, 'setActive', 'isActive']
        ];
    }

}
