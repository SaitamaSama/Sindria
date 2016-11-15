<?php
/**
 * Created by PhpStorm.
 * User: ragedwiz
 * Date: 12/11/16
 * Time: 3:00 PM
 */

namespace Sindria;
use Aerys\Response;
use Sindria\Application\Keys\Cookie;

class LogIn {
    const USERS = __DIR__ . '/Application/Storage/users.json';
    private $username;
    private $password;

    public function __construct(string $username, string $password) {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @throws \RuntimeException
     * @return self
     */
    public function do(): self {
        $users = json_decode(file_get_contents(static::USERS), true);

        if(!isset($users[$this->username])) {
            throw new \RuntimeException('The username does not exist...');
        }

        $details = $users[$this->username];
        if(!password_verify($this->password, $details['password'])) {
            throw new \RuntimeException('The username and password did not match...');
        }

        return $this;
    }

    public function storeCookie(Response $response, string $path = '/'): self {
        $response
            ->setCookie(Cookie::USERNAME, \base64_encode($this->username), [
                'path' => $path
            ])
            ->setCookie(Cookie::IS_LOGGED, true, [
                'path' => $path
            ]);
        return $this;
    }
}