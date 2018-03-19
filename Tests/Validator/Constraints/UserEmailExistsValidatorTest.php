<?php
namespace Wizjo\Bundle\UserBundle\Tests\Validator\Constraints;

use PHPUnit\Framework\TestCase;
use Wizjo\Bundle\UserBundle\User\UserManager;
use Wizjo\Bundle\UserBundle\User\UserManagerInterface;
use Wizjo\Bundle\UserBundle\User\UserManagerRegistryInterface;
use Wizjo\Bundle\UserBundle\Validator\Constraints\UserEmailExists;
use Wizjo\Bundle\UserBundle\Validator\Constraints\UserEmailExistsValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class UserEmailExistsValidatorTest extends TestCase
{
    /**
     * @var UserManagerRegistryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $managerRegistry;

    /**
     * @var UserManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $manager;

    public function setUp()
    {
        $this->managerRegistry = $this->createMock(UserManagerRegistryInterface::class);
        $this->manager = $this->createMock(UserManagerInterface::class);

        $this->managerRegistry
            ->method('getManagerForClass')
            ->willReturn($this->manager);

        $this->managerRegistry
            ->method('getManager')
            ->willReturn($this->manager);
    }

    public function tearDown()
    {
        $this->managerRegistry = null;
        $this->manager = null;
    }

    public function testValidateNoValue()
    {
        $validator = new UserEmailExistsValidator($this->managerRegistry);
        $constraint = new UserEmailExists(['entityClass' => \stdClass::class]);

        $this->manager
            ->expects(static::never())
            ->method('userExists');

        $validator->validate(null, $constraint);
    }

    public function testValidateNonObjectValue()
    {
        $validator = new UserEmailExistsValidator($this->managerRegistry);
        $constraint = new UserEmailExists(['entityClass' => \stdClass::class]);

        $this->expectException(UnexpectedTypeException::class);
        $validator->validate([], $constraint);
    }

    /**
     * @param $options
     * @param $email
     * @param $value
     *
     * @dataProvider validateWithoutViolationProvider
     */
    public function testValidateWithoutViolation($options, $email, $value)
    {
        $validator = new UserEmailExistsValidator($this->managerRegistry);
        $constraint = new UserEmailExists($options);

        $this->manager
            ->expects(static::once())
            ->method('userExists')
            ->with($email)
            ->willReturn(true);

        $validator->validate($value, $constraint);
    }

    public function validateWithoutViolationProvider()
    {
        $email = 'test@example.com';
        $attributeValue = new class {
            public $email;
        };
        $attributeValue->email = $email;

        $callbackValue = new class($email) {
            private $value;

            public function __construct($value)
            {
                $this->value = $value;
            }

            public function email() {
                return $this->value;
            }
        };

        return [
            [
                ['entityClass' => \stdClass::class],
                $email,
                $callbackValue
            ],
            [
                ['userManager' => \stdClass::class],
                $email,
                $attributeValue
            ]
        ];
    }

    public function testBuildViolation()
    {
        $email = 'test@example.com';

        $context = $this->createMock(ExecutionContextInterface::class);

        $validator = new UserEmailExistsValidator($this->managerRegistry);
        $validator->initialize($context);

        $constraint = new UserEmailExists(['entityClass' => \stdClass::class]);

        $value = new class {
            public $email;
        };
        $value->email = $email;

        $this->manager
            ->method('userExists')
            ->willReturn(false);

        $violation = $this->createMock(ConstraintViolationBuilderInterface::class);
        $context
            ->expects(static::once())
            ->method('buildViolation')
            ->with($constraint->message)
            ->willReturn($violation);

        $violation
            ->expects(static::once())
            ->method('setInvalidValue')
            ->with($email)
            ->willReturnSelf();

        $violation
            ->expects(static::once())
            ->method('atPath')
            ->with($constraint->path)
            ->willReturnSelf();

        $violation
            ->expects(static::once())
            ->method('setCode')
            ->with('baa23506-2837-426b-b684-f930e2e496ca')
            ->willReturnSelf();

        $violation
            ->expects(static::once())
            ->method('setParameter')
            ->with('{{ email }}', $email)
            ->willReturnSelf();

        $violation
            ->expects(static::once())
            ->method('addViolation');

        $validator->validate($value, $constraint);
    }
}
