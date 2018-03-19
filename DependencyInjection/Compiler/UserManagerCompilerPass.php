<?php
namespace Wizjo\Bundle\UserBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class UserManagerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('wizjo_user.user_manager_registry')) {
            return;
        }

        $registry = $container->findDefinition('wizjo_user.user_manager_registry');
        $configuration = $container->getParameter('wizjo_user.manager_config');

        foreach ($configuration as $id => $options) {
            $container
                ->findDefinition($id)
                ->setArgument('$userEntityClass', $options['entity']);

            $registry->addMethodCall(
                'addManager',
                [
                    new Reference($id),
                    $options['name']
                ]
            );
        }
    }
}
