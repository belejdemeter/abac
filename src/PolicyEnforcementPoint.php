<?php

namespace Core\Acl;

use Core\Acl\Contracts\PolicyEnforcementPoint as Contract;
use Core\Acl\Strategies\PolicyEnforcementStrategy;
use Core\Acl\Contracts\PolicyDecisionPoint as PDP;

class PolicyEnforcementPoint implements Contract
{
    /**
     * PDP
     * @var PDP
     */
    private $pdp;

    /**
     * Deny-biased or Permit-biased PEP strategy
     * @var PolicyEnforcementStrategy
     */
    private $strategy;

    /**
     * Class Constructor
     * @param PDP $pdp
     * @param PolicyEnforcementStrategy|null $strategy
     */
    public function __construct(PDP $pdp, ?PolicyEnforcementStrategy $strategy = null)
    {
        $this->pdp = $pdp;
        if ($strategy) $this->strategy = $strategy;
    }

    /**
     * Set PEP strategy
     * @param PolicyEnforcementStrategy|string $strategy
     */
    public function setStrategy(PolicyEnforcementStrategy $strategy)
    {
        $this->strategy = $strategy;
    }

    /**
     * Send a decision request to PDP and returns the response to the PEP.
     * @param Request $request
     * @return Response
     */
    public function request(Request $request)
    {
        $decision = $this->pdp->evaluate($request);

        return new Response(
            $this->getFinalDecision($decision),
            $request->getObligations(),
            $request->getAdvices()
        );
    }

    /**
     * Returns 'Permit' or 'Deny' according to the selected strategy.
     * @param  string $decision
     * @return string
     */
    private function getFinalDecision(string $decision)
    {
        return $this->strategy->getDecision($decision);
    }
}