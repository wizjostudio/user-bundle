<?php
namespace Wizjo\Bundle\UserBundle\Tests\DependencyInjection\Compiler;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Wizjo\Bundle\UserBundle\DependencyInjection\Compiler\UserManagerCompilerPass;

class UserManagerCompilerPassTest extends TestCase
{
    /**
     * @var ContainerBuilder|MockObject
     */
    private $container;

    protected function setUp()
    {
        $this->container = $this->createMock(ContainerBuilder::class);
    }

    protected function tearDown()
    {
        $this->container = null;
    }

    public function testProcessNoRegistryDefinition()
    {
        $this->container
            ->expects(static::once())
            ->method('has')
            ->with('wizjo_user.user_manager_registry')
            ->willReturn(false);

        $this->container
            ->expects(static::never())
            ->method('findDefinition');

        $pass = new UserManagerCompilerPass();
        $pass->process($this->container);
    }

    public function testProcess()
    {
        $registryDefinition = $this->createMock(Definition::class);
        $managerDefinition = $this->createMock(Definition::class);

        $config = [
            'manager.service_1' => [
                'entity' => \stdClass::class,
                'name' => 'test1'
            ],
            'manager.service_2' => [
                'entity' => \stdClass::class,
                'name' => 'test2'
            ],
        ];

        $this->container
            ->expects(static::once())
            ->method('has')
            ->with('wizjo_user.user_manager_registry')
            ->willReturn(true);


        $this->container
            ->expects(static::atLeastOnce())
            ->method('findDefinition')
            ->withConsecutive(
                ['wizjo_user.user_manager_registry'],
                ['manager.service_1'],
                ['manager.service_2']
            )
            ->willReturnOnConsecutiveCalls(
                $registryDefinition,
                $managerDefinition,
                $managerDefinition
            );

        $this->container
            ->expects(static::once())
            ->method('getParameter')
            ->with('wizjo_user.manager_config')
            ->willReturn($config);

        $managerDefinition
            ->expects(static::atLeastOnce())
            ->method('setArgument')
            ->with('$userEntityClass', \stdClass::class);

        $registryDefinition
            ->expects(static::atLeastOnce())
            ->method('addMethodCall')
            ->withConsecutive(
                ['addManager', static::callback($this->getCallbackForAddManagerCallArguments('test1'))],
                ['addManager', static::callback($this->getCallbackForAddManagerCallArguments('test2'))]
            );

        $pass = new UserManagerCompilerPass();
        $pass->process($this->container);
    }

    /**
     * @param string $name
     *
     * @return \Closure
     */
    private function getCallbackForAddManagerCallArguments(string $name)
    {
        return function (array $data) use ($name) {
            return count($data) === 2 &&
                   static::isInstanceOf(Reference::class, $data[0]) &&
                   $data[1] === $name;
        };
    }
}
