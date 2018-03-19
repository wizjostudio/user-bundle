<?php
namespace Wizjo\Bundle\UserBundle\Tests\User;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Happyr\DoctrineSpecification\EntitySpecificationRepository;
use Happyr\DoctrineSpecification\Exception\NoResultException;
use Happyr\DoctrineSpecification\Specification\Specification;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Wizjo\Bundle\UserBundle\Entity\User;
use Wizjo\Bundle\UserBundle\Entity\UserInterface;
use Wizjo\Bundle\UserBundle\Exception\UserNotFoundException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Wizjo\Bundle\UserBundle\Security\SecurityUser;
use Wizjo\Bundle\UserBundle\User\UserManager;
use Wizjo\Bundle\UserBundle\User\UserManagerInterface;

class UserManagerTest extends TestCase
{
    /**
     * @var EntityManagerInterface|MockObject
     */
    private $em;

    /**
     * @var EncoderFactoryInterface|MockObject
     */
    private $encoderFactory;

    /**
     * @var PasswordEncoderInterface|MockObject
     */
    private $passwordEncoder;

    /**
     * @var EventDispatcherInterface|MockObject
     */
    private $eventDispatcher;

    /**
     * @var User|MockObject
     */
    private $user;

    /**
     * @var string
     */
    private $entityClass;

    /**
     * @var string
     */
    private $entityLogical;

    public function setUp()
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->encoderFactory = $this->createMock(EncoderFactoryInterface::class);
        $this->passwordEncoder = $this->createMock(PasswordEncoderInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->user = $this->getMockForAbstractClass(
            User::class,
            ['test@exmaple.com', 'test']
        );
        $this->entityClass = get_class($this->user);
        $this->entityLogical = 'App:Test';

        $this->encoderFactory
            ->method('getEncoder')
            ->willReturn($this->passwordEncoder);

        $metadata = $this->createMock(ClassMetadata::class);
        $metadata
            ->method('getName')
            ->willreturn($this->entityClass);

        $this->em
            ->method('getClassMetadata')
            ->willReturn($metadata);
    }

    public function tearDown()
    {
        $this->em = null;
        $this->encoderFactory = null;
        $this->passwordEncoder = null;
        $this->eventDispatcher = null;
        $this->user = null;
        $this->entityClass = null;
    }

    public function testIsInstanceOfUserManagerInterface()
    {
        $manager = new UserManager($this->em, $this->encoderFactory, $this->entityClass);

        static::assertInstanceOf(UserManagerInterface::class, $manager);
    }

    public function testGetSecurityUserClass()
    {
        $manager = new UserManager($this->em, $this->encoderFactory, $this->entityClass);

        static::assertEquals(SecurityUser::class, $manager->getSecurityUserClass());
    }

    public function testGetUser()
    {
        $user = $this->createMock(UserInterface::class);
        $email = 'test@example.com';

        $repository = $this->createMock(EntitySpecificationRepository::class);
        $repository
            ->expects(static::once())
            ->method('matchSingleResult')
            ->with(static::isInstanceOf(Specification::class), null)
            ->willReturn($user);

        $this->em
            ->expects(static::once())
            ->method('getRepository')
            ->with($this->entityClass)
            ->willReturn($repository);

        $manager = new UserManager($this->em, $this->encoderFactory, $this->entityClass);
        $result = $manager->getUser($email);

        static::assertEquals($user, $result);
    }

    public function testGetNonExistentUser()
    {
        $email = 'test@example.com';

        $repository = $this->createMock(EntitySpecificationRepository::class);
        $repository
            ->expects(static::once())
            ->method('matchSingleResult')
            ->willThrowException(new NoResultException());

        $this->em
            ->expects(static::once())
            ->method('getRepository')
            ->willReturn($repository);

        $manager = new UserManager($this->em, $this->encoderFactory, $this->entityClass);

        $this->expectException(UserNotFoundException::class);
        $manager->getUser($email);
    }

    public function testUserExistsUserFound()
    {
        $email = 'test@example.com';

        $user = $this->createMock(UserInterface::class);

        $repository = $this->createMock(EntitySpecificationRepository::class);
        $repository
            ->expects(static::once())
            ->method('matchSingleResult')
            ->with(static::isInstanceOf(Specification::class), null)
            ->willReturn($user);

        $this->em
            ->expects(static::once())
            ->method('getRepository')
            ->willReturn($repository);

        $manager = new UserManager($this->em, $this->encoderFactory, $this->entityClass);
        $result = $manager->userExists($email);

        static::assertTrue($result);
    }

    public function testUserExistsUserNotFound()
    {
        $email = 'test@example.com';

        $repository = $this->createMock(EntitySpecificationRepository::class);
        $repository
            ->expects(static::once())
            ->method('matchSingleResult')
            ->willThrowException(new NoResultException());

        $this->em
            ->expects(static::once())
            ->method('getRepository')
            ->willReturn($repository);

        $manager = new UserManager($this->em, $this->encoderFactory, $this->entityClass);
        $result = $manager->userExists($email);

        static::assertFalse($result);
    }

    public function testUpdatePassword()
    {
        $password = 'secret';
        $encodedPassword = 'password';

        $this->passwordEncoder
            ->expects(static::once())
            ->method('encodePassword')
            ->with($password, '')
            ->willReturn($encodedPassword);

        $user = $this->createMock(UserInterface::class);
        $user
            ->expects(static::once())
            ->method('setPassword')
            ->with($encodedPassword);

        $this->em
            ->expects(static::once())
            ->method('flush');

        $manager = new UserManager($this->em, $this->encoderFactory, $this->entityClass);
        $manager->updatePassword($user, $password);
    }

    public function testPasswordValidation()
    {
        $password = 'secret';
        $encodedPassword = 'password';

        $this->passwordEncoder
            ->expects(static::once())
            ->method('isPasswordValid')
            ->with($encodedPassword, $password)
            ->willReturn(true);

        $manager = new UserManager($this->em, $this->encoderFactory, $this->entityClass);
        $result = $manager->checkPassword($encodedPassword, $password);

        static::assertTrue($result);
    }

    public function testCreateUser()
    {
        $email = 'test@example.com';
        $name = 'John Doe';

        $data = [
            'email' => $email,
            'name' => $name
        ];

        $this->em
            ->expects(static::once())
            ->method('persist')
            ->with(static::isInstanceOf(UserInterface::class));

        $this->em
            ->expects(static::once())
            ->method('flush');

        $manager = new UserManager($this->em, $this->encoderFactory, $this->entityClass);

        $user = $manager->createUser($data);

        static::assertInstanceOf(UserInterface::class, $user);
    }

    public function testUpdateRoles()
    {
        $user = $this->createMock(UserInterface::class);
        $roles = ['ROLE_TEST'];

        $user
            ->expects(static::once())
            ->method('setRoles')
            ->with($roles);

        $this->em
            ->expects(static::once())
            ->method('flush');

        $manager = new UserManager($this->em, $this->encoderFactory, $this->entityClass);
        $manager->updateRoles($user, $roles);
    }

    public function testUpdateUser()
    {
        $email = 'test@example.com';
        $name = 'John Doe';

        $user = $this->createMock(UserInterface::class);

        $data = [
            'email' => $email,
            'name' => $name,
        ];

        $user
            ->expects(static::once())
            ->method('setName')
            ->with($name);

        $user
            ->expects(static::once())
            ->method('setEmail')
            ->with($email);

        $this->em
            ->expects(static::once())
            ->method('flush');

        $manager = new UserManager($this->em, $this->encoderFactory, $this->entityClass);
        $manager->updateUser($user, $data);
    }

    /**
     * @param $data
     * @param $expectedException
     *
     * @dataProvider createAndUpdateUserInvalidDataProvider
     */
    public function testCreateUserInvalidData($data, $expectedException)
    {
        $this->expectException($expectedException);

        $manager = new UserManager($this->em, $this->encoderFactory, $this->entityClass);
        $manager->createUser($data);
    }

    /**
     * @param $data
     * @param $expectedException
     *
     * @dataProvider createAndUpdateUserInvalidDataProvider
     */
    public function testUpdateUserInvalidData($data, $expectedException)
    {
        $this->expectException($expectedException);

        $manager = new UserManager($this->em, $this->encoderFactory, $this->entityClass);
        $user = $this->createMock(UserInterface::class);

        $manager->updateUser($user, $data);
    }

    /**
     * @return array
     */
    public function createAndUpdateUserInvalidDataProvider()
    {
        return [
            [[], MissingOptionsException::class],
            [['test' => ''], UndefinedOptionsException::class],
        ];
    }
}
