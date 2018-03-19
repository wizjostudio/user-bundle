<?php
namespace Wizjo\Bundle\UserBundle\Tests\Validator\Constraints;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Exception\MissingOptionsException;
use Wizjo\Bundle\UserBundle\Entity\User;
use Wizjo\Bundle\UserBundle\Validator\Constraints\UserEmailNotExists;

class UserEmailNotExistsTest extends TestCase
{
    /**
     * @param array $options
     * @param bool $expectException
     *
     * @dataProvider requiredOptionsProvider
     */
    public function testRequiredOptions(array $options, bool $expectException)
    {
        if ($expectException) {
            $this->expectException(MissingOptionsException::class);
        }

        $constraint = new UserEmailNotExists($options);

        if (!$expectException) {
            foreach ($options as $option => $value) {
                static::assertEquals($value, $constraint->{$option});
            }
        }
    }

    public function requiredOptionsProvider()
    {
        return [
            [[], true,],
            [['entityClass' => ''], false,],
            [['userManager' => ''], false,],
        ];
    }

    public function testTarget()
    {
        $expectedTarget = 'class';

        $constraint = new UserEmailNotExists(['entityClass' => User::class]);

        static::assertEquals($expectedTarget, $constraint->getTargets());
    }
}
