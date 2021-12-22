<?php

namespace GarradinLDAP;

use FreeDSx\Ldap\Server\RequestHandler\GenericRequestHandler;
use FreeDSx\Ldap\Entry\Entries;
use FreeDSx\Ldap\Entry\Entry;
use FreeDSx\Ldap\Operation\Request\SearchRequest;
use FreeDSx\Ldap\Server\RequestContext;
use FreeDSx\Ldap\Server\Token\BindToken;

require_once __DIR__ . '/vendor/autoload.php';

class LdapRequestHandler extends GenericRequestHandler
{
	public function __construct()
	{
		$this->db = new \SQLite3(DB_FILE, \SQLITE3_OPEN_READONLY);
	}

	/**
	 * Validates the username/password of a simple bind request
	 *
	 * @param string $username
	 * @param string $password
	 * @return bool
	 */
	public function bind(string $username, string $password): bool
	{
		if (!preg_match('/uid=([^,=]+)/', $username, $match)) {
			throw new \InvalidArgumentException('Invalid username: ' . $username);
		}

		$login_field = $this->db->querySingle('SELECT value FROM config WHERE key = \'champ_identifiant\';');

		$sql = sprintf('SELECT passe FROM membres WHERE %s = \'%s\';', $login_field, $this->db->escapeString($match[1]));
		$stored_password = $this->db->querySingle($sql);

		if (!$stored_password) {
			return false;
		}

		return password_verify($password, $stored_password);
	}

	protected function getUser(BindToken $token)
	{
		$st = $this->db->prepare('SELECT * FROM membres WHERE email = ?;');
		$st->bindValue(1, $token->getUsername());
		$res = $st->execute();
		return (object) $res->fetchArray(\SQLITE3_ASSOC);
	}

	/**
	 * Override the search request. This must send back an entries object.
	 *
	 * @param RequestContext $context
	 * @param SearchRequest $search
	 * @return Entries
	 */
	public function search(RequestContext $context, SearchRequest $search): Entries
	{
		$user = $this->getUser($context->token());

		if (!$user) {
			return new Entries();
		}

		$query = $search->getFilter()->getValue();

		$login_field = $this->db->querySingle('SELECT value FROM config WHERE key = \'champ_identifiant\';');
		$id_field = $this->db->querySingle('SELECT value FROM config WHERE key = \'champ_identite\';');

		$st = $this->db->prepare(sprintf('SELECT * FROM membres WHERE %s = ?;', $login_field));
		$st->bindValue(1, $query);
		$res = $st->execute();

		$entries = new Entries;

		while ($row = $res->fetchArray(\SQLITE3_ASSOC)) {
			$row = (object) $row;
			$entries->add(Entry::create(sprintf('cn=%s,dc=garradin', $row->$id_field), [
				'cn' => $row->$id_field,
			]));
		}

		var_dump($query);
		var_dump($entries);

		return $entries;
	}
}

use FreeDSx\Ldap\LdapServer;

require_once __DIR__ . '/config.php';

$server = new LdapServer([
	'request_handler' => LdapRequestHandler::class,
	'port'            => $_SERVER['argv'][1] ?? 389,
]);

$server->run();
