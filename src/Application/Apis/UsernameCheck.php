<?php
/**
 * Created by PhpStorm.
 * User: ragedwiz
 * Date: 9/11/16
 * Time: 6:08 PM
 */

namespace Sindria\Application\Apis;
class UsernameCheck {
    private $username;

    const USERS = __DIR__ . '/../Storage/users.json';

    public function __construct(string $username) {
        $this->username = $username;

        return $this;
    }

    public function exists(): bool {
        $users = json_decode(file_get_contents(static::USERS), true);

        return isset($users[$this->username]);
    }
}