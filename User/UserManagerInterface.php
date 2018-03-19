<?php
namespace Wizjo\Bundle\UserBundle\User;

use Happyr\DoctrineSpecification\EntitySpecificationRepositoryInterface;
use Happyr\DoctrineSpecification\Specification\Specification;
use Wizjo\Bundle\UserBundle\Entity\UserInterface;
use Wizjo\Bundle\UserBundle\Exception\UserNotFoundException;

interface UserManagerInterface
{
    /**
     * @return string|EntitySpecificationRepositoryInterface
     */
    public function getSecurityUserClass(): string;

    /**
     * @return string
     */
    public function getUserEntityClass(): string;

    /**
     * @param string $email
     *
     * @return UserInterface
     * @throws UserNotFoundException
     */
    public function getUser(string $email): UserInterface;


    /**
     * @param Specification $specification
     *
     * @return UserInterface
     * @throws UserNotFoundException
     */
    public function getUserBySpec(Specification $specification): UserInterface;

    /**
     * @param string $email
     *
     * @return bool
     */
    public function userExists(string $email): bool;

    /**
     * @param UserInterface $user
     * @param string $password
     */
    public function updatePassword(UserInterface $user, string $password): void;

    /**
     * @param string $encodedPassword
     * @param string $password
     *
     * @return bool
     */
    public function checkPassword(string $encodedPassword, string $password): bool;

    /**
     * @param array $data
     *
     * @return UserInterface
     */
    public function createUser(array $data): UserInterface;

    /**
     * @param UserInterface $user
     * @param array $data
     */
    public function updateUser(UserInterface $user, array $data): void;

    /**
     * @param UserInterface $user
     * @param string[] $roles
     */
    public function updateRoles(UserInterface $user, array $roles): void;
}
