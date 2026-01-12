<?php

declare(strict_types=1);

namespace IfCastle\DI;

use IfCastle\DI\Dependencies\SomeClass;
use PHPUnit\Framework\TestCase;

class ContainerBuilderTest extends TestCase
{
    public function testIsBound(): void
    {
        $builder                    = new ContainerBuilder();
        $builder->bind('test', $this->createStub(DependencyInterface::class));
        $this->assertTrue($builder->isBound('test'));
        $this->assertFalse($builder->isBound('nonexistent'));

        $container                  = $builder->buildContainer($this->createStub(ResolverInterface::class));
        $this->assertTrue($container->findKey('test') !== null);
    }

    public function testBind(): void
    {
        $builder                    = new ContainerBuilder();
        $dependency                 = $this->createStub(DependencyInterface::class);
        $builder->bind('test', $dependency);
        $this->assertTrue($builder->isBound('test'));

        $container                  = $builder->buildContainer($this->createStub(ResolverInterface::class));
        $this->assertTrue($container->findKey('test') !== null);
    }

    public function testBindConstructible(): void
    {
        $builder                    = new ContainerBuilder();
        $builder->bindConstructible('test', SomeClass::class);
        $this->assertTrue($builder->isBound('test'));

        $container                  = $builder->buildContainer($this->createStub(ResolverInterface::class));
        $this->assertTrue($container->findKey('test') !== null);
    }

    public function testBindInjectable(): void
    {
        $builder                    = new ContainerBuilder();
        $builder->bindInjectable('test', SomeClass::class);
        $this->assertTrue($builder->isBound('test'));

        $container = $builder->buildContainer($this->createStub(ResolverInterface::class));
        $this->assertTrue($container->findKey('test') !== null);
    }

    public function testBindObject(): void
    {
        $builder                    = new ContainerBuilder();
        $object                     = new \stdClass();
        $builder->bindObject('test', $object);
        $this->assertTrue($builder->isBound('test'));

        $container                  = $builder->buildContainer($this->createStub(ResolverInterface::class));
        $this->assertTrue($container->findKey('test') !== null);
    }

    public function testSet(): void
    {
        $builder                    = new ContainerBuilder();
        $builder->set('test', 'value');
        $this->assertTrue($builder->isBound('test'));

        $container                  = $builder->buildContainer($this->createStub(ResolverInterface::class));
        $this->assertEquals('value', $container->resolveDependency('test'));
    }

    public function testBuildContainer(): void
    {
        $builder                    = new ContainerBuilder();
        $resolver                   = $this->createStub(ResolverInterface::class);
        $container                  = $builder->buildContainer($resolver);

        $this->assertInstanceOf(ContainerInterface::class, $container);
    }

    public function testGetKeyAsString(): void
    {
        $builder                    = new ContainerBuilder();

        $builder->bind('test', $this->createStub(DependencyInterface::class));
        $this->assertStringContainsString('object', $builder->getKeyAsString('test'));
    }

    public function testRedefineWithError(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $builder                    = new ContainerBuilder();
        $dependency1                = $this->createStub(DependencyInterface::class);
        $dependency2                = $this->createStub(DependencyInterface::class);

        $builder->bind('test', $dependency1);
        $builder->bind('test', $dependency2);
    }

    public function testRedefine(): void
    {
        $builder                    = new ContainerBuilder();
        $dependency1                = $this->createStub(DependencyInterface::class);
        $dependency2                = $this->createStub(DependencyInterface::class);

        $builder->bind('test', $dependency1);
        $this->assertTrue($builder->isBound('test'));

        $builder->bind('test', $dependency2, redefine: true);
        $this->assertTrue($builder->isBound('test'));
        $this->assertEquals($dependency2, $builder->get('test'));

        $container                  = $builder->buildContainer($this->createStub(ResolverInterface::class));
        $this->assertTrue($container->findKey('test') !== null);
    }
}
