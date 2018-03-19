<?php
namespace Wizjo\Bundle\UserBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\MissingOptionsException;

/**
 * @Annotation
 */
class UserEmailExists extends Constraint
{
    const EMAIL_NOT_EXISTS_ERROR = 'baa23506-2837-426b-b684-f930e2e496ca';

    protected static $errorNames = array(
        self::EMAIL_NOT_EXISTS_ERROR => 'EMAIL_NOT_EXISTS_ERROR',
    );

    public $message = 'This e-mail doesn\'t exist.';

    public $entityClass;
    public $userManager;
    public $path = 'email';
    public $email = 'email';

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
