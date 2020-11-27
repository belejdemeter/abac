<?php

namespace Core\Acl;

use Core\Acl\Contracts\Effect;

class Response
{
    /** @var string */
    public $decision;

    /** @var array|null */
    public $obligations;

    /** @var array|null */
    public $advices;

    /**
     * Result constructor.
     * @param string $decision
     * @param iterable|null $obligations
     * @param iterable|null $advices
     */
    public function __construct(string $decision, ?iterable $obligations = [], ?iterable $advices = [])
    {
        $this->decision = $decision;
        $this->obligations = $obligations;
        $this->advices = $advices;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->decision;
    }

    /**
     * @return bool
     */
    public function permit()
    {
        return $this->decision == Effect::PERMIT;
    }

    /**
     * @return bool
     */
    public function deny()
    {
        return $this->decision == Effect::DENY;
    }
}