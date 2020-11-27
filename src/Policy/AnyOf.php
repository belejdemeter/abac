<?php

namespace Core\Acl\Policy;

use Core\Acl\Contracts\Target;
use Core\Acl\Request;

class AnyOf
{
    /**
     * Disjunctive sequence of <AllOf> elements.
     * @var AllOf[]
     */
    protected $all_of_sequence;

    /**
     * AnyOf constructor.
     * @param array|null $sequence
     */
    public function __construct(?array $sequence)
    {
        foreach ($sequence as $row) {
            $this->all_of_sequence[] = ($row instanceof AllOf) ? $row : new AllOf($row);
        }
    }

    /**
     * The AnyOf SHALL match values in the request context
     * if at least one of their <AllOf> elements matches a value in the request context:
     * - At least one “Match” = “Match”
     * - All “No match” = “No match”
     * - None matches and at least one “Indeterminate” = “Indeterminate”
     * @param Request $request
     * @return string
     */
    public function evaluate(Request $request)
    {
        $results = [];

        /** @var AllOf */
        foreach ($this->all_of_sequence as $all_off) {
            $results[] = $all_off->evaluate($request);
        }

        if ($this->atLeastOneIn($results, Target::MATCH)) {
            return Target::MATCH;
        }
        elseif ($this->allValuesAre($results, Target::NO_MATCH)) {
            return  Target::NO_MATCH;
        }
        else {
            return Target::INDETERMINATE;
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
        return count(array_unique($haystack)) == 1 && end($haystack) == $needle;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'any_of' => array_map(function(AllOf $e) {
                return $e->toArray();
            }, $this->all_of_sequence)
        ];
    }
}