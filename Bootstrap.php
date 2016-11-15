<?php
/**
 * Created by PhpStorm.
 * User: ragedwiz
 * Date: 7/11/16
 * Time: 9:54 PM
 */

$router = Aerys\router()
    ->route('GET', '/login', function(\Aerys\Request $request, \Aerys\Response $response) {
        $templateLoader = new \Sindria\TemplateLoader();
        $response->end(
            $templateLoader->load('login.twig')
        );
        return;
    })
    ->route('POST', '/login', function(\Aerys\Request $request, \Aerys\Response $response) {
        $response->addHeader('Content-Type', 'application/json');

        if($request->getCookie(\Sindria\Application\Keys\Cookie::IS_LOGGED) === null) {
            /** @var \Aerys\ParsedBody $body */
            $body = yield \Aerys\parseBody($request);

            $username = $body->get('username');
            $password = $body->get('password');

            try {
                (new \Sindria\LogIn($username, $password))
                    ->do()
                    ->storeCookie($response);

                $response->end(json_encode([
                    'status' => 'success'
                ]));
            } catch (Throwable $throwable) {
                $response->end(json_encode([
                    'status' => 'failure',
                    'reason' => $throwable->getMessage()
                ]));
            }
        } else {
            $response->end(json_encode(
                ['status' => 'already-logged-in']
            ));
        }
        return;
    })
    ->route('GET', '/signup', function (\Aerys\Request $request, \Aerys\Response $response) {
        $response->end(
            (new Sindria\TemplateLoader())
                ->load('signup.twig')
        );
        return;
    })
    ->route('POST', '/signup', function (\Aerys\Request $request, \Aerys\Response $response) {
        /** @var \Aerys\ParsedBody $body */
        $body = yield Aerys\parseBody($request);

        $response->addHeader('Content-Type', 'application/json');

        try {
            (new \Sindria\SignUp($body->get('username'), $body->get('email'), $body->get('password')))
                ->store()
                ->storeCookie($response);

            $response->end(
                json_encode([
                    'status' => 'success'
                ])
            );
        } catch (\Throwable $ex) {
            $response->end(
                json_encode([
                    'status' => 'failure',
                    'reason' => $ex->getMessage()
                ])
            );
        }
        return;
    })
    ->route('GET', '/api/CheckUsername/{username}', function(\Aerys\Request $request, \Aerys\Response $response, array $args) {
        $exists = (new \Sindria\Application\Apis\UsernameCheck($args['username']))
            ->exists();
        $response->addHeader("Content-Type", "application/json");
        if($exists) {
            $response->end(
                json_encode([
                    'exists' => true
                ])
            );
        } else {
            $response->end(
                json_encode([
                    'exists' => false
                ])
            );
        }
        return;
    })
    ->route('GET', '/', function(\Aerys\Request $request, \Aerys\Response $response) {
        if($request->getCookie(\Sindria\Application\Keys\Cookie::IS_LOGGED) !== null && $request->getCookie(\Sindria\Application\Keys\Cookie::IS_LOGGED) == true) {
            $response->end(
                (new \Sindria\TemplateLoader())
                    ->load('chat.twig', [
                        'username' => base64_decode($request->getCookie(\Sindria\Application\Keys\Cookie::USERNAME))
                    ])
            );
        } else {
            $response
                ->addHeader('Location', '/login')
                ->setStatus(302)
                ->end();
        }
        return;
    })
    ->route('GET', '/api/users/details/{username}', function (\Aerys\Request $request, \Aerys\Response $response, array $args) {
        $response->end(
            json_encode(
                (new \Sindria\Application\Apis\Users\Details($args['username']))
                    ->get()
            )
        );
    });

$router->route('GET', '/chatWebSocket', \Aerys\websocket((new Sindria\Application\WebSockets\Chat())));

(new Aerys\Host())
    ->expose('*', 2001)
    ->use($router)
    ->use(\Aerys\root(__DIR__ . '/Resources'));