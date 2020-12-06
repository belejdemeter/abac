<?php

namespace Core\Acl\Policy;

use Core\Acl\Contracts\CombiningAlgorithm;
use Core\Acl\Contracts\Effect;
use Core\Acl\Request;
use Ramsey\Uuid\Uuid;

class Policy
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
     * The rule-combining algorithm.
     * @var CombiningAlgorithm
     */
    protected $algorithm;

    /**
     * Rules.
     * @var Rule[]
     */
    protected $rules;

    /**
     * Class Constructor.
     * @param iterable|null $data
     */
    public function __construct(?iterable $data)
    {
        $this->fill($data);
        if (!$this->id) $this->id = (string)Uuid::uuid4();
    }

    /**
     * @param iterable|null $data
     */
    public function fill(?iterable $data)
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $this->castAttribute($key, $value);
            }
        }
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    protected function castAttribute(string $key, $value = null)
    {
        if ($key == 'target') {
            if ($value instanceof Target) return $value;
            if (is_iterable($value)) return new Target($value);
            return null;
        }
        if ($key == 'algorithm') {
            return $value;
        }
        if ($key == 'rules') {
            return array_map(function($e) {
                if ($e instanceof Rule) return $e;
                if (is_iterable($e)) return new Rule($e);
                return null;
            }, $value);
        }
        return $value;
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
     * Evaluate expressions.
     * @param Request $request
     * @return mixed
     */
    public function evaluate(Request $request)
    {
        $target_eval_result = $this->evaluateTarget($request);
        $rule_eval_result = $this->evaluateRules($request);

        $result = $this->getDecision($rule_eval_result, $target_eval_result);

        return $result;
    }

    /**
     * 7.12 Policy evaluation.
     * |-------------------|-----------------------|-------------------------------------------
     * | Target            | Rule values           | Policy Value
     * |-------------------|-----------------------|-------------------------------------------
     * | “Match”           | Don’t care            | Specified by the rule-combining algorithm
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
     * @param string|null $rule_eval
     * @param string|null $target
     * @return mixed
     */
    protected function getDecision(?string $rule_eval, ?string $target)
    {
        if ($target == Target::MATCH) return $rule_eval;
        if ($target == Target::NO_MATCH) return Effect::NOT_APPLICABLE;
        if ($target == Target::INDETERMINATE) {
            if ($rule_eval == Effect::NOT_APPLICABLE) return Effect::NOT_APPLICABLE;
            if ($rule_eval == Effect::PERMIT || $rule_eval == Effect::INDETERMINATE_P) return Effect::INDETERMINATE_P;
            if ($rule_eval == Effect::DENY || $rule_eval == Effect::INDETERMINATE_D) return Effect::INDETERMINATE_D;
            if ($rule_eval == Effect::INDETERMINATE || $rule_eval == Effect::INDETERMINATE_DP) return Effect::INDETERMINATE_DP;
        }
        return Target::INDETERMINATE;
    }

    /**
     * Evaluate each rule and retrieve an array of decisions.
     * Decision can be the rules effect (Permit, Deny) or NotApplicable, Indeterminate
     * @param Request $request
     * @return string
     */
    private function evaluateRules(Request $request)
    {
        $values = [];
        /** @var Rule */
        foreach ($this->rules as $rule) {
            $values[] = $rule->evaluate($request);
        }
        return $this->combine($values);
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
            'rules' => array_map(function(Rule $rule) {
                return $rule->toArray();
            }, $this->rules)
        ];
    }
}