<?php
namespace Wizjo\Bundle\UserBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\MissingOptionsException;

/**
 * @Annotation
 */
class UserEmailNotExists extends Constraint
{
    const EMAIL_EXISTS_ERROR = '8e6c16c9-07eb-481c-b08f-ec776c505f77';

    protected static $errorNames = array(
        self::EMAIL_EXISTS_ERROR => 'EMAIL_EXISTS_ERROR',
    );

    public $message = 'This e-mail address already exists.';

    public $path = 'email';

    public $entityClass;
    public $userManager;
    public $email = 'email';
    public $oldEmail;

    public function __construct($options = null)
    {
        parent::__construct($options);

        $required = ['entityClass', 'userManager'];
        if (!is_array($options) || !$this->hasRequiredOption($required, $options)) {
            throw new MissingOptionsException(
                sprintf('One of the options "%s" must be set for constraint %s', implode('", "', array_keys($required)), get_class($this)),
                array_keys($required)
            );
        }
    }

    /**
     * @return string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    /**
     * @param array $required
     * @param array $options
     *
     * @return bool
     */
    private function hasRequiredOption(array $required, array $options): bool
    {
        foreach ($required as $item) {
            if (array_key_exists($item, $options)) {
                return true;
            }
        }

        return false;
    }
}
