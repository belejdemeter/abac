<?php

namespace Core\Acl\Io;

use Core\Acl\Policy\AllOf;
use Core\Acl\Policy\AnyOf;
use Core\Acl\Policy\Match;
use Core\Acl\Policy\Policy;
use Core\Acl\Policy\PolicySet;
use Core\Acl\Policy\Rule;
use Core\Acl\Policy\Target;
use Exception;

class ArrayParser
{
    /**
     * Note: The <PolicySet> element is a top-level element in the policy schema
     * @param $node
     * @return mixed
     */
    public function parse($node)
    {
        // <PolicySet> is an aggregation of other policy sets or policies
        return $this->parsePolicySet($node);

//        elseif (isset($node['rules'])) {
//            return$this->parsePolicy($node);
//        } else {
//            throw new Exception('The root element must be a policy set');
//        }
    }


    private function parsePolicySet(array $attributes)
    {
        $payload = [
            'id' => isset($attributes['id']) ? $attributes['id'] : null,
            'description' => isset($attributes['description']) ? $attributes['description'] : null,
            'algorithm' => $this->parseAlgorithm($attributes),
            'target' => $this->parseTarget($attributes),
            'policies' => $this->parsePolicies(isset($attributes['policies']) ? $attributes['policies'] : null),
        ];
        return new PolicySet($payload);
    }


    protected function parsePolicies($nodes)
    {
        $policies = [];
        foreach ($nodes as $node) {
            if (isset($node['policies'])) {
                // The node is policy set
                $policies[] = $this->parsePolicySet($node);
            } else {
                // The node is a policy
                $policies[] = $this->parsePolicy($node);
            }

        }
        return $policies;
    }

    protected function parsePolicy($attributes)
    {
        $payload = [
            'id' => isset($attributes['id']) ? $attributes['id'] : null,
            'description' => isset($attributes['description']) ? $attributes['description'] : null,
            'target' => $this->parseTarget($attributes),
            'algorithm' => $this->parseAlgorithm($attributes),
            'rules' => $this->parseRules($attributes['rules']),
        ];
        return new Policy($payload);
    }

    private function parseTarget($attributes)
    {
        if (!isset($attributes['target'])) {
            return null; // Match in any case....
        } else {
            $any_ofs = $this->parseAnyOfSequence(data_get($attributes, 'target'));
            return new Target($any_ofs);
        }
    }

    private function parseAnyOfSequence($nodes)
    {
        return array_map(function ($e) {
            $data = data_get($e, 'any_of', []);
            return new AnyOf($this->parseAllOfSequence($data));
        }, $nodes);
    }

    private function parseAllOfSequence($nodes)
    {
        return array_map(function ($e) {
            $data = data_get($e, 'all_of', []);
            return new AllOf($this->parseMatchSequence($data));
        }, $nodes);
    }

    private function parseMatchSequence(array $nodes)
    {
        return array_map(function ($e) {
            return $this->parseMatch($e);
        }, $nodes);
    }

    private function parseMatch($attributes)
    {
        $payload = [
            'match_id' => data_get($attributes, 'match_id', null),
            'attribute_value' => data_get($attributes, 'attribute_value', null),
            'attribute_designator' => data_get($attributes, 'attribute_designator', null),
            'attribute_selector' => data_get($attributes, 'attribute_selector', null),
        ];
        return new Match($payload);
    }

    protected function parseAlgorithm($attributes)
    {
        $algorithm_class = isset($attributes['algorithm']) ? $attributes['algorithm'] : 'DenyOverrides';
        $algorithm_class_ns = '\\Core\\Acl\\Algorithm\\' . $algorithm_class;

        if (class_exists($algorithm_class_ns)) {
            return $algorithm_class_ns;
        } else {
            throw new Exception("Unknown combining algorithm provided ($algorithm_class_ns).");
        }
    }

    private function parseRules($rules)
    {
        return array_map(function ($attributes) {
            return $this->parseRule($attributes);
        }, $rules);
    }

    private function parseRule(array $attributes)
    {
        $payload = [
            'id' => data_get($attributes, 'id', null),
            'description' => data_get($attributes, 'description', null),
            'target' => $this->parseTarget($attributes),
            'effect' => data_get($attributes, 'effect', null),
            'condition' => data_get($attributes, 'condition', null),
            'obligation' => data_get($attributes, 'obligation', []),
            'advice' => data_get($attributes, 'advice', []),
        ];
        return new Rule($payload);
    }


}