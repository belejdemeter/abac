<?php 

namespace Core\Acl\Strategies;

use Core\Acl\Contracts\Effect;

/**
 * 7.2.2 Deny-biased PEP
 * 
 * If the decision is "Permit", then the PEP SHALL permit access.  
 * If obligations accompany the decision, then the PEP SHALL permit access only 
 * if it understands and it can and will discharge those obligations.
 * All other decisions SHALL result in the denial of access.
 * Note: other actions, e.g. consultation of additional PDPs, 
 * reformulation/resubmission of the decision request, etc., are not prohibited.
 */
class DenyBiasedStrategy implements PolicyEnforcementStrategy
{

	public function getDecision($decision)
	{
		if ($decision == Effect::PERMIT) return Effect::PERMIT;

		return Effect::DENY;
	}
}