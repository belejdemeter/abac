<?php 

namespace Core\Acl\Strategies;

use Core\Acl\Contracts\Effect;

/**
 * 7.2.3 Permit-biased PEP
 * 
 * If the decision is "Deny", then the PEP SHALL deny access.  
 * If obligations accompany the decision, then the PEP shall deny access only if it understands, 
 * and it can and will discharge those obligations.
 * All other decisions SHALL result in the permission of access.
 * Note: other actions, e.g. consultation of additional PDPs, 
 * reformulation/resubmission of the decision request, etc., are not prohibited.
 */
class PermitBiasedStrategy implements PolicyEnforcementStrategy
{
	public function getDecision($decision)
	{
		if ($decision == Effect::DENY) return Effect::DENY;

		return Effect::PERMIT;
	}
}