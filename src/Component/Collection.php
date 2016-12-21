<?php

namespace Egg\Component;

class Collection extends \Slim\Collection
{
    public function set($class, $component)
    {
        if ($this->has($class)) {
            throw new \Exception(sprintf('Component "%s" already registered', $class));
        }

        parent::set($class, $component);
    }

    public function stack()
    {
        return $this->resolveGraph();
    }

    protected function resolveGraph()
    {
        $classStack = [];
        foreach ($this as $componentClass => $component) {
            $classStack = $this->resolveGraphItem($componentClass, $classStack);
        }

        $componentStack = [];
        foreach ($classStack as $componentClass) {
            $componentStack[] = $this->get($componentClass);
        }

        return $componentStack;
    }

    protected function resolveGraphItem($componentClass, array $classStack, array $testedStack = [])
    {
        if (in_array($componentClass, $classStack)) {
            return $classStack;
        }

        if (in_array($componentClass, $testedStack)) {
            throw new \Exception(sprintf('Circular dependency of component "%s"', $componentClass));
        }

        $component = $this->get($componentClass);
        $dependencies = $component->getDependencies();
        if (empty($dependencies)) {
            if (!in_array($componentClass, $classStack)) {
                $classStack[] = $componentClass;
            }
        }
        else {
            $testedStack[] = $componentClass;
            foreach ($dependencies as $dependencyClass) {
                $dependencyComponent = $this->get($dependencyClass);
                if (!$dependencyComponent) {
                    throw new \Exception(sprintf('Component "%s" required by "%s"', $dependencyClass, $componentClass));
                }
                $classStack = $this->resolveGraphItem($dependencyClass, $classStack, $testedStack);
            }
            if (!in_array($componentClass, $classStack)) {
                $classStack[] = $componentClass;
            }
        }

        return $classStack;
    }
}