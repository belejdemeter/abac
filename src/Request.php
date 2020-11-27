<?php

namespace Core\Acl;

use ArrayAccess;
use Illuminate\Support\Collection;
use LogicException;

class Request implements ArrayAccess
{
    /** @var string[] */
    protected $categories = [
        'action',
        'subject',
        'resource',
        'environment'
    ];

    /** @var array */
    protected $attributes = [];

    /** @var iterable|null */
    protected $obligations = [];

    /** @var iterable|null */
    protected $advices = [];


    /**
     * Class Constructor
     * @param iterable|null $data
     */
    public function __construct(?iterable $data)
    {
        foreach ($data as $category => $attributes) {
            if (in_array($category, $this->categories)) {
                $this->set($category, $attributes);
            } else {
                throw new LogicException("Unsupported category '$category' for request context.");
            }
        }
    }

    /**
     * @return iterable|null
     */
    public function getObligations(): ?iterable
    {
        return $this->obligations;
    }

    /**
     * @param iterable|null $obligations
     */
    public function addObligation(?iterable $obligations): void
    {
        $this->obligations[] = $obligations;
    }

    /**
     * @return iterable|null
     */
    public function getAdvices(): ?iterable
    {
        return $this->advices;
    }

    /**
     * @param iterable|null $advices
     */
    public function addAdvice(?iterable $advices): void
    {
        $this->advices[] = $advices;
    }


    /**
     * @param string $key
     * @return array|mixed|null
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Return the value of a given key
     * @param int|string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key = null, $default = null)
    {
        return data_get($this->attributes, $key, $default);
    }

    /**
     * Set a given key / value pair or pairs
     * @param array|int|string $keys
     * @param mixed $value
     */
    public function set($keys, $value = null)
    {
        data_set($this->attributes, $keys, $value);
    }

    /**
     * Check if a given key exists
     * @param int|string $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return property_exists($this, $key);
    }

    /**
     * Return the value of a given key
     * @param int|string $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * Set a given value to the given key
     * @param int|string $key
     * @param mixed $value
     */
    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Delete the given key
     * @param int|string $key
     */
    public function offsetUnset($key)
    {
        $this->set($key, null);
    }

}