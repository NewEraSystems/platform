<?php

namespace Oro\Bundle\ScopeBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\ScopeBundle\DependencyInjection\Compiler\ScopeProviderPass;
use Oro\Bundle\ScopeBundle\Manager\ScopeManager;
use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ServiceLocator;

class ScopeProviderPassTest extends \PHPUnit\Framework\TestCase
{
    /** @var ScopeProviderPass */
    private $compiler;

    public function setUp()
    {
        $this->compiler = new ScopeProviderPass();
    }

    public function testProcessNoProviders()
    {
        $container = new ContainerBuilder();
        $managerDef = $container->register(
            'oro_scope.scope_manager',
            ScopeManager::class
        );

        $this->compiler->process($container);

        $managerServiceLocatorRef = $managerDef->getArgument(0);
        self::assertInstanceOf(Reference::class, $managerServiceLocatorRef);
        $managerServiceLocatorDef = $container->getDefinition((string)$managerServiceLocatorRef);
        self::assertEquals(ServiceLocator::class, $managerServiceLocatorDef->getClass());
        self::assertEquals([], $managerServiceLocatorDef->getArgument(0));
        self::assertEquals([], $managerDef->getArgument(1));
    }

    public function testProcess()
    {
        $container = new ContainerBuilder();
        $managerDef = $container->register(
            'oro_scope.scope_manager',
            ScopeManager::class
        );

        $container->register('service.name.1')
            ->addTag('oro_scope.provider', ['scopeType' => 'scope', 'priority' => 100])
            ->addTag('oro_scope.provider', ['scopeType' => 'scope2']);
        $container->register('service.name.2')
            ->addTag('oro_scope.provider', ['scopeType' => 'scope', 'priority' => 1])
            ->addTag('oro_scope.provider', ['scopeType' => 'scope2', 'priority' => 100]);
        $container->register('service.name.3')
            ->addTag('oro_scope.provider', ['scopeType' => 'scope', 'priority' => 200]);

        $this->compiler->process($container);

        $managerServiceLocatorRef = $managerDef->getArgument(0);
        self::assertInstanceOf(Reference::class, $managerServiceLocatorRef);
        $managerServiceLocatorDef = $container->getDefinition((string)$managerServiceLocatorRef);
        self::assertEquals(ServiceLocator::class, $managerServiceLocatorDef->getClass());
        self::assertEquals(
            [
                'service.name.1' => new ServiceClosureArgument(new Reference('service.name.1')),
                'service.name.2' => new ServiceClosureArgument(new Reference('service.name.2')),
                'service.name.3' => new ServiceClosureArgument(new Reference('service.name.3'))
            ],
            $managerServiceLocatorDef->getArgument(0)
        );
        self::assertEquals(
            [
                'scope'  => ['service.name.3', 'service.name.1', 'service.name.2'],
                'scope2' => ['service.name.2', 'service.name.1']
            ],
            $managerDef->getArgument(1)
        );
    }
}
