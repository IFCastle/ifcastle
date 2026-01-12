<?php

declare(strict_types=1);

namespace IfCastle\DI;

use IfCastle\DI\Dependencies\CircularDependency1;
use IfCastle\DI\Dependencies\CircularDependency2;
use IfCastle\DI\Dependencies\CircularDependencyWrong1;
use IfCastle\DI\Dependencies\CircularDependencyWrong2;
use IfCastle\DI\Dependencies\ClassWithDependencyContact;
use IfCastle\DI\Dependencies\ClassWithLazyDependency;
use IfCastle\DI\Dependencies\ClassWithNoExistDependency;
use IfCastle\DI\Dependencies\CustomDescriptorClass;
use IfCastle\DI\Dependencies\ObjectWithDependencyContact;
use IfCastle\DI\Dependencies\UseConstructorClass;
use IfCastle\DI\Dependencies\UseConstructorInterface;
use IfCastle\DI\Dependencies\UseInjectableClass;
use IfCastle\DI\Dependencies\UseInjectableInterface;
use IfCastle\DI\Exceptions\CircularDependencyException;
use IfCastle\DI\Exceptions\DependencyNotFound;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    protected Container $container;

    #[\Override]
    protected function setUp(): void
    {
        $builder                    = new ContainerBuilder();
        $builder->bindConstructible([UseConstructorInterface::class, 'alias1'], UseConstructorClass::class);
        $builder->bindInjectable([UseInjectableInterface::class, 'alias2'], UseInjectableClass::class);
        $builder->bindConstructible('wrong_dependency', ClassWithNoExistDependency::class);
        $builder->bindConstructible('lazy_dependency', ClassWithLazyDependency::class);
        $builder->bindConstructible(CircularDependency1::class, CircularDependency1::class);
        $builder->bindConstructible(CircularDependency2::class, CircularDependency2::class);
        $builder->bindConstructible(CircularDependencyWrong1::class, CircularDependencyWrong1::class);
        $builder->bindConstructible(CircularDependencyWrong2::class, CircularDependencyWrong2::class);
        $builder->bindSelfReference();

        $this->container            = $builder->buildContainer(new Resolver());
    }

    /**
     * @throws DependencyNotFound
     */
    public function testResolveDependencyByKey(): void
    {
        $class1                     = $this->container->resolveDependency(UseConstructorInterface::class);
        $this->assertInstanceOf(UseConstructorClass::class, $class1);

        $class2                     = $this->container->resolveDependency(UseInjectableInterface::class);
        $this->assertInstanceOf(UseInjectableClass::class, $class2);

        $this->assertEquals($class1, $this->container->resolveDependency('alias1'));
        $this->assertEquals($class2, $this->container->resolveDependency('alias2'));
    }

    public function testDependencyNotFound(): void
    {
        $this->expectException(DependencyNotFound::class);
        $this->container->resolveDependency('non-existent');
    }

    public function testDependencyNotFoundMessage(): void
    {
        try {
            $this->container->resolveDependency('non-existent');
        } catch (DependencyNotFound $exception) {
            $this->assertStringContainsString(__FILE__ . ':' . __LINE__ - 2, $exception->getMessage());
        }
    }

    public function testDependencyNotFoundMessageForOtherDependency(): void
    {
        try {
            $this->container->resolveDependency('wrong_dependency');
        } catch (DependencyNotFound $exception) {
            $this->assertStringContainsString(__FILE__ . ':' . __LINE__ - 2, $exception->getMessage());
        }
    }

    public function testResolveNotRequiredDependency(): void
    {
        $result = $this->container->resolveDependency(new Dependency('non-existent', null, false));
        $this->assertNull($result);
    }

    public function testSelfReference(): void
    {
        $result = $this->container->resolveDependency(ContainerInterface::class);

        $this->assertEquals($this->container, $result);
    }

    public function testLazyLoad(): void
    {
        $result = $this->container->resolveDependency('lazy_dependency');

        $this->assertInstanceOf(ClassWithLazyDependency::class, $result);
        $this->assertInstanceOf(UseConstructorClass::class, $result->some);
        $result->some->someMethod();
    }

    /**
     * @throws DependencyNotFound
     */
    public function testWeakReference(): void
    {
        $object                     = new \stdClass();
        $container                  = new Container(new Resolver(), ['stdClass' => \WeakReference::create($object)]);

        $this->assertEquals($object, $container->resolveDependency('stdClass'));
    }

    public function testCircularDependency(): void
    {
        $dependency1                = $this->container->resolveDependency(CircularDependency1::class);
        $dependency2                = $this->container->resolveDependency(CircularDependency2::class);

        $this->assertInstanceOf(CircularDependency2::class, $dependency1->getDependency2());
        $this->assertInstanceOf(CircularDependency1::class, $dependency2->getDependency1());
    }

    public function testCircularDependencyWrong(): void
    {
        $this->expectException(CircularDependencyException::class);
        $this->container->resolveDependency(CircularDependencyWrong1::class);
    }

    public function testDescriptorProvider(): void
    {
        $builder                    = new ContainerBuilder();
        $builder->bindConstructible('customKey', UseConstructorClass::class);
        $builder->bindConstructible(CustomDescriptorClass::class, CustomDescriptorClass::class);

        $container                  = $builder->buildContainer(new Resolver());

        $dependency                 = $container->resolveDependency(CustomDescriptorClass::class);

        $this->assertInstanceOf(CustomDescriptorClass::class, $dependency);
    }

    public function testDependencyContract(): void
    {
        $builder                    = new ContainerBuilder();
        $builder->bindConstructible('specificKey', ObjectWithDependencyContact::class);
        $builder->bindConstructible(ClassWithDependencyContact::class, ClassWithDependencyContact::class);

        $container                  = $builder->buildContainer(new Resolver());

        $dependency                 = $container->resolveDependency(ClassWithDependencyContact::class);

        $this->assertInstanceOf(ClassWithDependencyContact::class, $dependency);
    }
}
