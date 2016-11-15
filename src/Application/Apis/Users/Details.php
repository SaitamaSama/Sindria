<?php
/**
 * Created by PhpStorm.
 * User: ragedwiz
 * Date: 13/11/16
 * Time: 1:00 PM
 */

namespace Sindria\Application\Apis\Users;
class Details {
    private $username;

    const USERS = __DIR__ . '/../../Storage/users.json';

    public function __construct(string $username) {
        $this->username = $username;
    }

    public function get(): array {
        $rawDetails = json_decode(file_get_contents(static::USERS), true);
        unset($rawDetails['password']);

        return $rawDetails;
    }
}