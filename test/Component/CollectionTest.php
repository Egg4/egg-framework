<?php

namespace Egg\Component;

use Egg\Component\Collection as ComponentCollection;
use Egg\Component\Noop as NoopComponent;
use Egg\Container;

class CollectionTest extends \Egg\Test
{
    public function testShouldRaiseComponentAlreadyRegisteredException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Component "Class1" already registered');

        $collection = new ComponentCollection();
        $collection->set('Class1', new NoopComponent());
        $collection->set('Class1', new NoopComponent());
    }

    public function testShouldRaiseComponentRequiredException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Component "Class2" required by "Class1"');

        $collection = new ComponentCollection();
        $collection->set('Class1', new NoopComponent(['Class2']));
        $collection->stack();
    }

    public function testShouldRaiseCircularDependencyException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Circular dependency of component "Class1"');

        $collection = new ComponentCollection();
        $collection->set('Class1', new NoopComponent(['Class2']));
        $collection->set('Class2', new NoopComponent(['Class3', 'Class5']));
        $collection->set('Class3', new NoopComponent());
        $collection->set('Class4', new NoopComponent(['Class2']));
        $collection->set('Class5', new NoopComponent(['Class1']));
        $collection->stack();
    }

    public function testShouldResolveDependencyGraph()
    {
        $components = [
            new NoopComponent(),
            new NoopComponent(['Class3', 'Class4']),
            new NoopComponent(),
            new NoopComponent(['Class4', 'Class5']),
            new NoopComponent(),
            new NoopComponent(['Class0']),
        ];

        $collection = new ComponentCollection();
        $collection->set('Class0', $components[0]);
        $collection->set('Class1', $components[1]);
        $collection->set('Class2', $components[2]);
        $collection->set('Class3', $components[3]);
        $collection->set('Class4', $components[4]);
        $collection->set('Class5', $components[5]);
        $stack = $collection->stack();

        $this->assertEquals($stack[0], $components[0]);
        $this->assertEquals($stack[1], $components[4]);
        $this->assertEquals($stack[2], $components[5]);
        $this->assertEquals($stack[3], $components[3]);
        $this->assertEquals($stack[4], $components[1]);
        $this->assertEquals($stack[5], $components[2]);
    }
}