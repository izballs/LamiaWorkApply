<?php
declare(strict_types=1);

namespace Lamia\Common;

use Psr\Container\ContainerInterface;
Class Container implements ContainerInterface {

    protected $entries = [];
    protected $constructArguments = [];


    public function get($id)
    {
        if(!$this->has($id))
            $this->set($id);

        if($this->entries[$id] instanceof \Closure || is_callable($this->entries[$id]))
            return $this->entries[$id]($this);

        if(isset($this->rules['shared']) && in_array($id, $this->rules['shared']))
            return $this->singleton($id);

        return $this->resolve($id);

    }

    public function has($id)
    {
        return isset($this->entries[$id]);
    }

    public function set($abstract, $concrete = null){
        if($concrete === null)
            $concrete = $abstract;
        $this->entries[$abstract] = $concrete;
    }

    public function setParameters($id, $fields){
        foreach($fields as $key => $value)
        {
            $this->constructArguments[$id][$key] = $value;
        }
    }

    public function resolve($alias)
    {
        $reflector = $this->getReflector($alias);
        $constructor = $reflector->getConstructor();
        if(!$reflector->isInstantiable())
            throw new ContainerException("Cannot inject{$reflector->getName()} to {$class} because it cannot be instantiated");
        if($constructor === null)
            return $reflector->newInstance();
        
        $args = $this->getArguments($alias, $constructor);
        return $reflector->newInstanceArgs($args);
    }

    public function getReflector($alias)
    {
        $class = $this->entries[$alias];
        try 
        {
            return (new \ReflectionClass($class));
        }
        catch (\ReflectionException $e)
        {
            throw new NotFoundException(
                $e->getMessage(), $e->getCode()
            );
        }
    }

    public function getArguments($alias, \ReflectionMethod $constructor)
    {
        $args = [];
        $parameters = $constructor->getParameters();
        foreach ($parameters as $parameter) {
            if($parameter->getClass() !== null)
            {
                $args[] = $this->get($parameter->getClass()->getName());
            }
            else if (isset($this->constructArguments[$alias][$parameter->getName()]))
            {
                $args[] = $this->constructArguments[$alias][$parameter->getName()];
            }
        }
        return $args;
    }

    public function configure(array $config)
    {
        $this->rules = array_merge($this->rules,$config);
        return $this;
    }
    public function setup($id){
        
    }
}
?>
