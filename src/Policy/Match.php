<?php

namespace Core\Acl\Policy;

use Core\Acl\Contracts\Match as Contract;
use Core\Acl\Exception\ProcessingError;
use Core\Acl\Functions\AbstractFunction;
use Core\Acl\Request;

class Match implements Contract
{
    /**
     * Specifies a matching function.
     * @var mixed
     */
    protected $match_id;

    /**
     * Embedded attribute value.
     * @var mixed|null
     */
    protected $attribute_value;

    /**
     * Retrieves a bag of values for a named attribute from the request context
     * Used to identify one or more attribute values in an <Attributes> element of the request context.
     * @var string|null
     */
    protected $attribute_designator;

    /**
     * Produces a bag of unnamed and uncategorized attribute values.
     * Be used to identify one or more attribute values in a <Content> element of the request context.
     * @var string|null
     */
    protected $attribute_selector;

    /**
     * Match constructor.
     * @param iterable|null $data
     */
    public function __construct(?iterable $data)
    {
        foreach (get_object_vars($this) as $key => $default) {
            $this->{$key} = isset($data[$key]) ? $data[$key] : null;
        }
    }

    /**
     * If at least one of those function applications were to evaluate to "True",
     * then the result of the entire expression SHALL be "True".
     * Otherwise, if at least one of the function applications results in "Indeterminate",
     * then the result SHALL be "Indeterminate".
     * Finally, if all function applications evaluate to "False",
     * then the result of the entire expression SHALL be "False".
     * @param Request $request
     * @return string
     */
    public function evaluate(Request $request)
    {
        $result = $this->processExpression($request);

        if ($result === true) {
            $match = Contract::TRUE;
        } elseif ($result === false) {
            $match = Contract::FALSE;
        } else {
            $match = Contract::INDETERMINATE;
        }

        return $match;
    }

    /**
     * @param Request $request
     * @return bool|string
     */
    private function processExpression(Request $request)
    {
        $fn = $this->match_id;
        $fn = ucwords(str_replace(['-', '_'], ' ', $fn));
        $fn = str_replace(' ', '', $fn);
        $fn_class = "\\Core\\Acl\\Functions\\$fn";

        if (!class_exists($fn_class)) {
            throw new ProcessingError("Function $this->match_id not found");
        }
        /** @var AbstractFunction $fn_instance */
        $fn_instance = new $fn_class();

        $designator = $this->attribute_designator;
        $selector = $this->attribute_selector;
        $value = $this->attribute_value;

        $match = $fn_instance->evaluate($request, $designator, $selector, $value);

        return $match;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }
}