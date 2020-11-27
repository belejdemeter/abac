<?php

namespace Core\Acl\Policy;

use Core\Acl\Contracts\Target as Contract;
use Core\Acl\Request;

class Target implements Contract
{
    /**
     * Conjunctive sequence of <AnyOf> elements
     * @var AnyOf[]
     */
    protected $any_of_sequence;

    /**
     * Target constructor.
     * @param iterable|null $sequence
     */
    public function __construct(?iterable $sequence)
    {
        foreach ($sequence as $row) {
            $this->any_of_sequence[] = ($row instanceof AnyOf) ? $row : new AnyOf($row);
        }
    }

    /**
     * 7.7 Target evaluation
     * An empty target matches any request.
     * Otherwise the target value SHALL be "Match" if all the AnyOf specified in the target match values in the request context.
     * Otherwise, if any one of the AnyOf specified in the target is “No Match”, then the target SHALL be “No Match”.
     * Otherwise, the target SHALL be “Indeterminate”.
     * @param Request $request
     * @return string
     */
    public function evaluate(Request $request)
    {
        if (count($this->any_of_sequence) > 0) {
            $results = [];

            foreach ($this->any_of_sequence as $any_of) {
                $results[] = $any_of->evaluate($request);
            }

            if ($this->allValuesAre($results, Contract::MATCH)) {
                return Contract::MATCH;
            } elseif ($this->atLeastOneIn($results, Contract::NO_MATCH)) {
                return Contract::NO_MATCH;
            } else {
                return Contract::INDETERMINATE;
            }
        } else {
            return Contract::MATCH;
        }
    }

    /**
     * @param array $haystack
     * @param string $needle
     * @return bool
     */
    protected function atLeastOneIn(array $haystack, string $needle)
    {
        return in_array($needle, $haystack);
    }

    /**
     * @param array $haystack
     * @param string $needle
     * @return bool
     */
    protected function allValuesAre(array $haystack, string $needle)
    {
        return count(array_unique($haystack)) == 1 && end($haystack) === $needle;
    }

    /**
     * @return array|array[]
     */
    public function toArray()
    {
        return array_map(function (AnyOf $e) {
            return $e->toArray();
        }, $this->any_of_sequence);
    }
}