<?php

namespace Core\Acl\Repository;

use Core\Acl\Contracts\PolicyRepository;

class InMemoryRepository implements PolicyRepository
{
    /** @var array */
    private $data;

    /**
     * InMemoryRepository constructor.
     */
    public function __construct()
    {

    }

    /**
     * @param $data
     */
    public function set($data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function load()
    {
        return $this->data;
    }
}