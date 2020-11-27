<?php

namespace Core\Acl\Policy;

use Core\Acl\Contracts\Effect;
use Core\Acl\Contracts\Target;
use Core\Acl\Request;
use Ramsey\Uuid\Uuid;

class Rule
{
    /**
     * Uniqe id. Can be removed.
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
     * @var Target|null
     */
    protected $target;

    /**
     * The effect of the rule indicates the rule-writer's intended consequence of a "True" evaluation for the rule.
     * Two values are allowed: "Permit" and "Deny".
     * @var string
     */
    protected $effect;

    /**
     * Condition represents a Boolean expression that refines the applicability of the rule
     * beyond the predicates implied by its target. Therefore, it may be absent.
     * @var string|null
     */
    protected $condition = null;

    /**
     * @todo Obligation expressions
     * @var mixed
     */
    protected $obligation = null;

    /**
     * @todo Advice expressions
     * @var mixed
     */
    protected $advice = null;


    /**
     * Class Constructor.
     * @param iterable|null $data
     */
    public function __construct(?iterable $data)
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) $this->{$key} = $value;
        }
        if (!$this->id) $this->id = (string)Uuid::uuid4();
    }

    /**
     * Evaluate expressions.
     * @param Request $request
     * @return mixed
     */
    public function evaluate(Request $request)
    {
        $target = $this->evaluateTarget($request);
        $condition = $this->evaluateCondition($request);

        return $this->getFinalDecision($condition, $target);
    }

    /**
     * Evaluate target expression.
     * An empty target matches any request.
     * Otherwise the target value SHALL be 'Match', 'No match', 'Indeterminate'.
     * @param Request $request
     * @return bool|null|'Indeterminate'
     */
    public function evaluateTarget(Request $request)
    {
        if (empty($this->target)) return Target::MATCH;
        return $this->target->evaluate($request);
    }

    /**
     * Evaluate condition expression.
     * The condition value SHALL be "True" if the condition is absent, or if it evaluates to "True".
     * Its SHALL be "False" if the Condition evaluates to "False".
     * The condition value SHALL be "Indeterminate", if the expression contained in the Condition evaluates to "Indeterminate."
     * @param Request $request
     * @return true|false|'Indeterminate'
     */
    public function evaluateCondition(Request $request)
    {
        // @todo Now it's fine...
        return 'True';
    }

    /**
     * 7.11 Rule evaluation
     * A rule has a value that can be calculated by evaluating its contents.
     * Rule evaluation involves separate evaluation of the rule's target and condition.
     * The rule truth table is shown bellow.
     * +------------------------+-------------------+-----------------------+
     * |  TARGET                |  CONDITION        |    RULE VALUE         |
     * +------------------------+-------------------+-----------------------+
     * |  “Match” or no target  |  “True”           |    Effect             |
     * |  “Match” or no target  |  “False”          |    “NotApplicable”    |
     * |  “Match” or no target  |  “Indeterminate”  |    “Indeterminate”    |
     * |  “No-match”            |  Don’t care       |    “NotApplicable”    |
     * |  “Indeterminate”       |  Don’t care       |    “Indeterminate”    |
     * +------------------------+-------------------+-----------------------+
     * @param mixed $condition_eval
     * @param mixed $target_eval
     */
    private function getFinalDecision($condition_eval, $target_eval)
    {
        if (($target_eval == Target::MATCH || !$this->target) && $condition_eval == 'True') {
            return $this->effect;
        }
        if (($target_eval == Target::MATCH || !$this->target) && $condition_eval == 'False') {
            return Effect::NOT_APPLICABLE;
        }
        if (($target_eval == Target::MATCH || !$this->target) && $condition_eval == Effect::INDETERMINATE) {
            return Effect::INDETERMINATE;
        }
        if ($target_eval == Target::NO_MATCH) {
            return Effect::NOT_APPLICABLE;
        }
        if ($target_eval == Target::INDETERMINATE) {
            return Effect::INDETERMINATE;
        }
        return Effect::INDETERMINATE;
    }

    /**
     * Magic getter.
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        if (property_exists($this, $key)) return $this->{$key};
    }

    /**
     * Magic setter.
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        if (property_exists($this, $key)) $this->{$key} = $value;
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
            'target' => ($this->target instanceof \Core\Acl\Policy\Target) ? $this->target->toArray() : null,
            'effect' => $this->effect,
            'condition' => $this->condition ?? null,
            'obligation' => $this->obligation ?? [],
            'advice' => $this->advice ?? [],
        ];
    }
}