<?php
namespace Wizjo\Bundle\UserBundle\Tests\Validator\Constraints;

use PHPUnit\Framework\TestCase;
use Wizjo\Bundle\UserBundle\User\UserManagerInterface;
use Wizjo\Bundle\UserBundle\User\UserManagerRegistryInterface;
use Wizjo\Bundle\UserBundle\Validator\Constraints\UserEmailNotExists;
use Wizjo\Bundle\UserBundle\Validator\Constraints\UserEmailNotExistsValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class UserEmailNotExistsValidatorTest extends TestCase
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
        $validator = new UserEmailNotExistsValidator($this->managerRegistry);
        $constraint = new UserEmailNotExists(['entityClass' => \stdClass::class]);

        $this->manager
            ->expects(static::never())
            ->method('userExists');

        $validator->validate(null, $constraint);
    }

    public function testValidateNonObjectValue()
    {
        $validator = new UserEmailNotExistsValidator($this->managerRegistry);
        $constraint = new UserEmailNotExists(['entityClass' => \stdClass::class]);

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
        $validator = new UserEmailNotExistsValidator($this->managerRegistry);
        $constraint = new UserEmailNotExists($options);

        $this->manager
            ->expects(static::once())
            ->method('userExists')
            ->with($email)
            ->willReturn(false);

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

    /**
     * @param $value
     *
     * @dataProvider validateWithOldEmailSameAsEmail
     */
    public function testValidateWithOldEmailSameAsEmail($value)
    {
        $validator = new UserEmailNotExistsValidator($this->managerRegistry);
        $constraint = new UserEmailNotExists([
            'oldEmail' => 'oldEmail',
            'entityClass' => \stdClass::class,
        ]);

        $this->manager
            ->expects(static::never())
            ->method('userExists');

        $validator->validate($value, $constraint);
    }

    public function validateWithOldEmailSameAsEmail()
    {
        $email = 'test@example.com';
        $attributeValue = new class {
            public $email;
            public $oldEmail;
        };
        $attributeValue->email = $email;
        $attributeValue->oldEmail = $email;

        $callbackValue = new class($email) {
            private $value;

            public function __construct($value)
            {
                $this->value = $value;
            }

            public function email() {
                return $this->value;
            }

            public function oldEmail() {
                return $this->value;
            }
        };

        return [
            [$callbackValue],
            [$attributeValue],
        ];
    }

    public function testBuildViolation()
    {
        $email = 'test@example.com';

        $context = $this->createMock(ExecutionContextInterface::class);

        $validator = new UserEmailNotExistsValidator($this->managerRegistry);
        $validator->initialize($context);

        $constraint = new UserEmailNotExists(['entityClass' => \stdClass::class]);

        $value = new class {
            public $email;
        };
        $value->email = $email;

        $this->manager
            ->method('userExists')
            ->willReturn(true);

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
            ->with('8e6c16c9-07eb-481c-b08f-ec776c505f77')
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
