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
            $user = User::with('events')
                ->where('id','=',$args['id'])
                ->first();
            $res = [];
            foreach($user->events as $event_utilisateur){
                $event = Event::find($event_utilisateur->pivot->event_id);
                $res[] = $event;
            }


        } catch (ModelNotFoundException $e) {
            return Writer::jsonOutput($response, 404, ['message' => 'Event introuvable']);
        }

        return Writer::jsonOutput($response, 200, ['events' => $res]);
    }
}
