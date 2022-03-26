<?php

namespace reu\event\app\controller;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use reu\event\app\model\Event;
use reu\event\app\model\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use reu\event\app\utils\Writer;
use Slim\Container;

/**
 * Class UserController
 *
 * @author HUMBERT Lucas
 * @author BUDZIK Valentin
 * @author HOUQUES Baptiste
 * @author LAMBERT Calvin
 * @package reu\event\app\controller
 *
 */

class UserController
{
    private $c;

    public function __construct(Container $c)
    {
        $this->c = $c;
    }

    public function getUsers(Request $request, Response $response)
    {
        $users = User::all();
        return Writer::jsonOutput($response, 200, [$users]);
    }

    public function getUsersEvents(Request $request, Response $response, $args)
    {
        try {
            $tokenstring = sscanf($request->getHeader('Authorization')[0], "Bearer %s")[0];
            $token = JWT::decode($tokenstring, new Key($this->c['secret'], 'HS512'));
            if($token->upr->id !== $args['id']){
                return Writer::jsonOutput($response, 401, ['error' => 'Unauthorized']);
            }
        }
        catch (\Exception $e){
            return Writer::jsonOutput($response, 403, ['message' => $e]);
        }
        try {
            $user = User::with('events')
                ->where('id','=',$args['id'])
                ->first();
            $res = [];
            foreach($user->events as $event_utilisateur){
                $event = Event::find($event_utilisateur->pivot->event_id);
                $res[] = $event;
            }

        } catch (ModelNotFoundException $e) {

            return Writer::jsonOutput($response, 404, ['message' => 'Events introuvable']);
        }

        return Writer::jsonOutput($response, 200, ['events' => $res]);
    }
}
