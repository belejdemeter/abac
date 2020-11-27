<?php 
namespace Core\Acl\Exception;

class MissingAttribute extends \RuntimeException
{
	public function error_message() {
		return 'urn:oasis:names:tc:xacml:1.0:status:missing-attribute';
	}
}