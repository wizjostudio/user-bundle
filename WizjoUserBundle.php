<?php
namespace Wizjo\Bundle\UserBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Wizjo\Bundle\UserBundle\DependencyInjection\Compiler\UserManagerCompilerPass;

class WizjoUserBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new UserManagerCompilerPass());
    }
}
