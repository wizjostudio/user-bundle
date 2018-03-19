<?php
namespace Wizjo\Bundle\UserBundle\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Wizjo\Bundle\UserBundle\DependencyInjection\Compiler\UserManagerCompilerPass;
use Wizjo\Bundle\UserBundle\WizjoUserBundle;

class WizjoUserBundleTest extends TestCase
{
    public function testInstanceOfBundle()
    {
        $bundle = new WizjoUserBundle();

        static::assertInstanceOf(Bundle::class, $bundle);
    }

    public function testCompilerPasses()
    {
        $bundle = new WizjoUserBundle();
        $container = $this->createMock(ContainerBuilder::class);

        $container
            ->expects(static::atLeastOnce())
            ->method('addCompilerPass')
            ->withConsecutive(
                [static::isInstanceOf(UserManagerCompilerPass::class)]
            );

        $bundle->build($container);
    }
}
