<?php
/**
 * Created by PhpStorm.
 * User: ragedwiz
 * Date: 8/11/16
 * Time: 9:55 PM
 */

namespace Sindria;
use Aerys\Response;
use Sindria\Application\Keys\Cookie;

class SignUp {
    private $username;
    private $password;
    private $users;
    private $email;

    const USERS = __DIR__ . '/Application/Storage/users.json';

    public function __construct(string $username, string $email, string $password) {
        $this->username = $username;
        $this->email = $email;
        $this->password = password_hash($password, PASSWORD_BCRYPT);
        $this->users = json_decode(file_get_contents(static::USERS), true);

        return $this;
    }

    public function store(): self {
        $this->users[$this->username] = [
            'email' => $this->email,
            'password' => $this->password
        ];
        file_put_contents(static::USERS, json_encode($this->users, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
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