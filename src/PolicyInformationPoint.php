<?php

namespace Core\Acl;

use Core\Acl\Attributes\ActionAttributeResolver;
use Core\Acl\Attributes\EnvironmentAttributeResolver;
use Core\Acl\Attributes\ResourceAttributeResolver;
use Core\Acl\Attributes\SubjectAttributeResolver;
use Core\Acl\Contracts\AttributeResolverInterface;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;
use Core\Acl\Contracts\PolicyInformationPoint as Contract;

class PolicyInformationPoint implements Contract
{
    /** @var Container */
    protected $container;

    /** @var string[] */
    protected $resolvers = [
        'action'        => ActionAttributeResolver::class,
        'environment'   => EnvironmentAttributeResolver::class,
        'resource'      => ResourceAttributeResolver::class,
        'subject'       => SubjectAttributeResolver::class,
    ];

    /**
     * PolicyInformationPoint constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $key
     * @param string $class
     */
    public function setResolver(string $key, string $class)
    {
        if (array_key_exists($key, $this->resolvers) && class_exists($class)) {
            $ref = new \ReflectionClass($class);
            if ($ref->implementsInterface(AttributeResolverInterface::class)) {
                $this->resolvers[$key] = $class;
            } else {
                throw new InvalidArgumentException('Class must implement the AttributeResolverInterface interface');
            }
        } else {
            throw new InvalidArgumentException("Class '$class' not exists or ID '$key' is invalid");
        }
    }

    /**
     * 8. The PIP returns the requested attributes to the context handler.
     * @param Request $request
     * @return array
     */
    public function requestAttributes(Request $request)
    {
        return $this->obtainRequestedAttributes($request);
    }

    /**
     * 7. The PIP obtains the requested attributes.
     * @param Request $request
     * @return array
     */
    protected function obtainRequestedAttributes(Request $request)
    {
        $registry = [];
        foreach ($this->resolvers as $class) {
            /** @var AttributeResolverInterface $resolver */
            $resolver = $this->container->make($class);

            $key = $resolver->category;
            $data = $resolver->getAttributes($request);
            $registry[$key] = $data;
        }
        return $registry;
    }
}