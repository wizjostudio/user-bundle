<?php
namespace Wizjo\Bundle\UserBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Wizjo\Bundle\UserBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class ConfigurationTest extends TestCase
{
    public function testGetConfigTreeBuilder()
    {
        $root = 'wizjo_user';

        $configuration = new Configuration();

        $result = $configuration->getConfigTreeBuilder();
        $nameResult = $result->buildTree()->getName();

        static::assertInstanceOf(TreeBuilder::class, $result);
        static::assertEquals($root, $nameResult);
    }
}
