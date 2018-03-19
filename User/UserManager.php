<?php
namespace Wizjo\Bundle\UserBundle\User;

use Happyr\DoctrineSpecification\EntitySpecificationRepositoryInterface;
use Happyr\DoctrineSpecification\Spec;
use Happyr\DoctrineSpecification\Specification\Specification;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wizjo\Bundle\UserBundle\Entity\UserInterface;
use Wizjo\Bundle\UserBundle\Security\SecurityUser;
use Doctrine\ORM\EntityManagerInterface;
use Happyr\DoctrineSpecification\Exception\NoResultException;
use Wizjo\Bundle\UserBundle\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class UserManager implements UserManagerInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var PasswordEncoderInterface
     */
    protected $passwordEncoder;

    /**
     * @var string
     */
    protected $userEntityClass;

    /**
     * @param EntityManagerInterface $em
     * @param EncoderFactoryInterface $encoderFactory
     * @param string $userEntityClass
     */
    public function __construct(EntityManagerInterface $em, EncoderFactoryInterface $encoderFactory, string $userEntityClass)
    {
        $this->em = $em;
        $this->passwordEncoder = $encoderFactory->getEncoder($this->getSecurityUserClass());
        $this->userEntityClass = $em->getClassMetadata($userEntityClass)->getName();
    }

    /**
     * @return string|EntitySpecificationRepositoryInterface
     */
    public function getSecurityUserClass(): string
    {
        return SecurityUser::class;
    }

    /**
     * @return string
     */
    public function getUserEntityClass(): string
    {
        return $this->userEntityClass;
    }

    /**
     * @param string $email
     *
     * @return UserInterface
     * @throws \Wizjo\Bundle\UserBundle\Exception\UserNotFoundException
     */
    public function getUser(string $email): UserInterface
    {
        return $this->getUserBySpec(Spec::andX(
            Spec::eq('email', $email)
        ));
    }

    /**
     * @param Specification $specification
     *
     * @return mixed
     * @throws UserNotFoundException
     */
    public function getUserBySpec(Specification $specification): UserInterface
    {
        try {
            $user = $this->em
                ->getRepository($this->getUserEntityClass())
                ->matchSingleResult($specification);

            return $user;
        } catch (NoResultException $e) {
            throw new UserNotFoundException('User not found');
        }
    }

    /**
     * @param string $email
     *
     * @return bool
     */
    public function userExists(string $email): bool
    {
        try {
            $this->getUser($email);
        } catch (UserNotFoundException $e) {
            return false;
        }

        return true;
    }

    /**
     * @param UserInterface $user
     * @param string $password
     */
    public function updatePassword(UserInterface $user, string $password): void
    {
        $user->setPassword(
            $this->passwordEncoder->encodePassword($password, '')
        );

        $this->em->flush();
    }

    /**
     * @param string $encodedPassword
     * @param string $password
     *
     * @return bool
     */
    public function checkPassword(string $encodedPassword, string $password): bool
    {
        return $this->passwordEncoder->isPasswordValid($encodedPassword, $password, '');
    }

    /**
     * @param array $data
     *
     * @return UserInterface
     */
    public function createUser(array $data): UserInterface
    {
        $data = $this->configureCreateOptions(new OptionsResolver())->resolve($data);

        $class = $this->getUserEntityClass();
        $user = new $class($data['email'], $data['name']);

        $this->applyCreateData($user, $data);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    /**
     * @param UserInterface $user
     * @param array $data
     */
    public function applyCreateData(UserInterface $user, array $data)
    {}

    /**
     * @param OptionsResolver $resolver
     *
     * @return OptionsResolver
     */
    public function configureCreateOptions(OptionsResolver $resolver): OptionsResolver
    {
        $resolver->setRequired(['email', 'name']);
        return $resolver;
    }

    /**
     * @param UserInterface $user
     * @param array $data
     */
    public function updateUser(UserInterface $user, array $data): void
    {
        $data = $this->configureUpdateOptions(new OptionsResolver(), $user)->resolve($data);

        $this->applyUpdateData($user, $data);

        $this->em->flush();
    }

    /**
     * @param UserInterface $user
     * @param array $data
     */
    public function applyUpdateData(UserInterface $user, array $data)
    {
        $user->setEmail($data['email']);
        $user->setName($data['name']);
    }

    /**
     * @param OptionsResolver $resolver
     * @param UserInterface $user
     *
     * @return OptionsResolver
     */
    public function configureUpdateOptions(OptionsResolver $resolver, UserInterface $user): OptionsResolver
    {
        $resolver->setRequired(['email', 'name']);
        $resolver->setDefined(['active']);

        return $resolver;
    }

    /**
     * @param UserInterface $user
     * @param string[] $roles
     */
    public function updateRoles(UserInterface $user, array $roles): void
    {
        $user->setRoles($roles);
        $this->em->flush();
    }
}
