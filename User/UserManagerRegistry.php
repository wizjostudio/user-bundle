<?php
namespace Wizjo\Bundle\UserBundle\User;

use Wizjo\Bundle\UserBundle\Exception\UserManagerNotFoundException;

class UserManagerRegistry implements UserManagerRegistryInterface
{
    /**
     * @var array|UserManagerInterface[]
     */
    private $managers = [];

    /**
     * @var array
     */
    private $entityMapping = [];

    /**
     * @param UserManagerInterface $manager
     * @param string $name
     */
    public function addManager(UserManagerInterface $manager, string $name): void
    {
        $this->managers[$name] = $manager;
        $this->entityMapping[$manager->getUserEntityClass()] = $name;
    }

    /**
     * @param string $entityClass
     *
     * @return UserManagerInterface
     * @throws UserManagerNotFoundException
     */
    public function getManagerForClass(string $entityClass): UserManagerInterface
    {
        if (!array_key_exists($entityClass, $this->entityMapping)) {
            throw new UserManagerNotFoundException(sprintf(
                'No manager found for entity class "%s"',
                $entityClass
            ));
        }

        return $this->getManager($this->entityMapping[$entityClass]);
    }

    /**
     * @param string $name
     *
     * @return UserManagerInterface
     * @throws UserManagerNotFoundException
     */
    public function getManager(string $name): UserManagerInterface
    {
        if (!array_key_exists($name, $this->managers)) {
            throw new UserManagerNotFoundException(sprintf(
                'No manager found named "%s"',
                $name
            ));
        }

        return $this->managers[$name];
    }
}
