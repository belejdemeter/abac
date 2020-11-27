<?php

namespace Core\Acl\Policy;

use Core\Acl\Request;


class AllOf
{
    /**
     * Conjunctive sequence of <Match> elements.
     * @var array|null
     */
    protected $matches;

    /**
     * AnyOf constructor.
     * @param array|null $matches
     */
    public function __construct(?array $matches)
    {
        $this->matches = $matches;
    }

    /**
     * An AllOf SHALL match a value in the request context if the value of all its <Match> elements is “True”.
     * - All “True” = “Match”
     * - No “False” and at least one “Indeterminate” = “Indeterminate”
     * - At least one “False” = “No match”
     * @param Request $request
     * @return string
     */
    public function evaluate(Request $request)
    {
        /** @var AllOf[] $matches */
        $matches = $this->matches;

        $results = [];

        /** @var Match $match */
        foreach ($matches as $match) {
            $results[] = $match->evaluate($request);
        }

        if ($this->allValuesAre($results, Match::TRUE)) {
            return \Core\Acl\Contracts\Target::MATCH;
        } elseif ($this->atLeastOneIn($results, Match::FALSE)) {
            return \Core\Acl\Contracts\Target::NO_MATCH;
        } else {
            return \Core\Acl\Contracts\Target::INDETERMINATE;
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
            'all_of' => array_map(function (Match $e) {
                return $e->toArray();
            }, $this->matches)
        ];
    }
}