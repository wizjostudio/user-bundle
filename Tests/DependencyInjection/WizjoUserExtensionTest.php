<?php
namespace Wizjo\Bundle\UserBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Wizjo\Bundle\UserBundle\DependencyInjection\WizjoUserExtension;
use Wizjo\Bundle\UserBundle\User\UserManagerInterface;

class WizjoUserExtensionTest extends TestCase
{
    public function testConfigurationLoading(): void
    {
        $filesToLoad = [
            'services.xml',
        ];

        $managersConfig = [
            'test' => [
                'entity' => \stdClass::class,
                'manager' => \stdClass::class,
            ],
        ];

        $expectedManagers = [
            \stdClass::class => [
                'entity' => \stdClass::class,
                'name' => 'test',
            ],
        ];

        $filesToLoadCallbacks = array_map(function ($file) {
            return static::callback(function ($v) use ($file) { return $this->callbackEndsWith($file, $v); });
        }, $filesToLoad);

        $container = $this->createMock(ContainerBuilder::class);

        $container
            ->expects(static::once())
            ->method('setParameter')
            ->with(
                'wizjo_user.manager_config',
                $expectedManagers
            );

        $container
            ->expects(static::atLeastOnce())
            ->method('fileExists')
            ->withConsecutive(...$filesToLoadCallbacks);

        $extension = new WizjoUserExtension();
        $extension->load(
            [
                ['managers' => $managersConfig]
            ],
            $container
        );
    }

    private function callbackEndsWith(string $haystack, string $needle): bool
    {
        $length = strlen($needle);
        if ($length === 0) {
            return false;
        }

        return (substr($haystack, -$length) === $needle);
    }
}
