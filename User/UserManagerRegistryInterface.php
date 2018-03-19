<?php
namespace Wizjo\Bundle\UserBundle\User;

interface UserManagerRegistryInterface
{
    /**
     * @param UserManagerInterface $manager
     * @param string $name
     */
    public function addManager(UserManagerInterface $manager, string $name): void;

    /**
     * @param string $entityClass
     *
     * @return UserManagerInterface
     */
    public function getManagerForClass(string $entityClass): UserManagerInterface;

    /**
     * @param string $name
     *
     * @return UserManagerInterface
     */
    public function getManager(string $name): UserManagerInterface;
}
