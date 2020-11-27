<?php

namespace Core\Acl\Policy;

use Core\Acl\Contracts\CombiningAlgorithm;
use Core\Acl\Contracts\Effect;
use Core\Acl\Contracts\Target;
use Core\Acl\Request;
use Ramsey\Uuid\Uuid;

class PolicySet
{
    /**
     * Unique id. Can be removed.
     * @var string
     */
    protected $id;

    /**
     * Description.
     * @var string
     */
    protected $description;

    /**
     * Target expression.
     * @var Target
     */
    protected $target;

    /**
     * Combining algorithm.
     * @var CombiningAlgorithm
     */
    protected $algorithm;

    /**
     * Policies.
     * @var Policy[]
     */
    protected $policies;

    /**
     * Class Constructor.
     * @param iterable|null $data
     * @throws \Exception
     */
    public function __construct(?iterable $data)
    {
        $this->id = (string)Uuid::uuid4();

        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) $this->{$key} = $value;
        }
    }

    /**
     * Magic getter
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        if (property_exists($this, $key)) return $this->{$key};
    }

    /**
     * Magic setter
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        if (property_exists($this, $key)) $this->{$key} = $value;
    }


    /**
     * Evaluate target expression and child policies.
     * @param Request $request
     * @return mixed
     */
    public function evaluate(Request $request)
    {
        $target_decision = $this->evaluateTarget($request);
        $policy_decision = $this->evaluatePolicies($request);
        return $this->getDecision($policy_decision, $target_decision);
    }

    /**
     * Evaluate target expression.
     * An empty target matches any request.
     * Otherwise the target value SHALL be 'Match', 'No match', 'Indeterminate'.
     * @param Request $request
     * @return string
     */
    public function evaluateTarget(Request $request)
    {
        if (empty($this->target)) return Target::MATCH;
        return $this->target->evaluate($request);
    }

    /**
     * Evaluate each policy and retrieve an array of decisions.
     * Decision can be the policy effect (Permit, Deny) or NotApplicable, Indeterminate
     * @param Request $request
     * @return string
     */
    private function evaluatePolicies(Request $request)
    {
        $values = [];
        /** @var Policy */
        foreach ($this->policies as $policy) {
            $values[] = $policy->evaluate($request);
        }
        return $this->combine($values);
    }

    /**
     * 7.12 Policy set evaluation.
     * |-------------------|-----------------------|-------------------------------------------
     * | Target            | Policy values         | Policy set Value
     * |-------------------|-----------------------|-------------------------------------------
     * | “Match”           | Don’t care            | Specified by the policy-combining algorithm
     * | “No-match”        | Don’t care            | “NotApplicable”
     * | “Indeterminate”   |                       |
     * |                   | “NotApplicable”       | “NotApplicable”
     * |                   | “Permit”              | “Indeterminate{P}”
     * |                   | “Deny”                | “Indeterminate{D}”
     * |                   | “Indeterminate”       | “Indeterminate{DP}”
     * |                   | “Indeterminate{DP}”   | “Indeterminate{DP}”
     * |                   | “Indeterminate{P}”    | “Indeterminate{P}”
     * |                   | “Indeterminate{D}”    | “Indeterminate{D}”
     * |-------------------|-----------------------|-------------------------------------------
     * @param string|null $rule
     * @param string|null $target
     * @return mixed
     */
    protected function getDecision(?string $rule, ?string $target)
    {
        if ($target == Target::MATCH) return $rule;
        if ($target == Target::NO_MATCH) return Effect::NOT_APPLICABLE;
        if ($target == Target::INDETERMINATE) {
            if ($rule == Effect::NOT_APPLICABLE) return Effect::NOT_APPLICABLE;
            if ($rule == Effect::PERMIT || $rule == Effect::INDETERMINATE_P) return Effect::INDETERMINATE_P;
            if ($rule == Effect::DENY || $rule == Effect::INDETERMINATE_D) return Effect::INDETERMINATE_D;
            if ($rule == Effect::INDETERMINATE || $rule == Effect::INDETERMINATE_DP) return Effect::INDETERMINATE_DP;
        }
        return Target::INDETERMINATE;
    }

    /**
     * Combine values using the given combining algorithm.
     * @param array|null $values
     * @return mixed
     */
    protected function combine(?array $values)
    {
        $class = $this->algorithm;
        $algorithm = new $class;
        return $algorithm->evaluate($values);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            # '_element' => __CLASS__,
            'id' => $this->id,
            'description' => $this->description,
            'target' => ($this->target instanceof Target) ? $this->target->toArray() : null,
            'algorithm' => substr(strrchr($this->algorithm, "\\"), 1),
            'policies' => array_map(function($policy_or_set) {
                return $policy_or_set->toArray();
            }, $this->policies)
        ];
    }
}