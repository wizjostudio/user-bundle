<?php
namespace Wizjo\Bundle\UserBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Wizjo\Bundle\UserBundle\User\UserManagerRegistryInterface;

class UserEmailExistsValidator extends ConstraintValidator
{
    /**
     * @var UserManagerRegistryInterface
     */
    private $userManagerRegistry;

    /**
     * @param UserManagerRegistryInterface $userManagerRegistry
     */
    public function __construct(UserManagerRegistryInterface $userManagerRegistry)
    {
        $this->userManagerRegistry = $userManagerRegistry;
    }

    /**
     * @param mixed $value
     * @param Constraint $constraint
     *
     * @throws UnexpectedTypeException
     */
    public function validate($value, Constraint $constraint): void
    {
        if ($value === null) {
            return;
        }

        if (!is_object($value)) {
            throw new UnexpectedTypeException($value, 'object');
        }

        if ($constraint->entityClass) {
            $manager = $this->userManagerRegistry->getManagerForClass($constraint->entityClass);
        } else {
            $manager = $this->userManagerRegistry->getManager($constraint->userManager);
        }

        if (is_callable([$value, $constraint->email])) {
            $email = $value->{$constraint->email}();
        } else {
            $email = $value->{$constraint->email};
        }

        if (!$manager->userExists($email)) {
            $this->context
                ->buildViolation($constraint->message)
                ->setInvalidValue($email)
                ->atPath($constraint->path)
                ->setCode(UserEmailExists::EMAIL_NOT_EXISTS_ERROR)
                ->setParameter('{{ email }}', $email)
                ->addViolation();
        }
    }
}
