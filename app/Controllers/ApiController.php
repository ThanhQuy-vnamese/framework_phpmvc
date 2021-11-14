<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth\Authentication;
use App\Core\Controller\BaseController;
use App\Core\Database\Query;
use App\Core\Lib\Token;

class ApiController extends BaseController {
    public function getUser()
    {
        $query = new Query();
        $users = $query->table('users')->get();
        $result = [];
        foreach ($users as $user) {
            $temp = [];
            $temp['name'] = $user->firstname . $user->lastname;
            $temp['isActive'] = (bool)$user->status;
            $result[] = $temp;
        }
        return $this->response->json_encode($result);
    }

    public function login() {
        $content = json_decode(file_get_contents('php://input'), true);
        $headers = $this->request->getHeaders();

        $authentication = new Authentication();
        $info = ['isLogin' => false];
        $isLogin = $authentication->login(['email' => $content['email'], 'password' => $content['password']]);
        if ($isLogin) {
            $idUser = $authentication->getUser()->id;
            $email = $authentication->getUser()->email;

            $payload = ['id' => $idUser, 'email' => $email];
            $token = new Token();
            $privateKey = 'dBjjuk0M7V';
            $token->setPayload($payload);
            $jwt = $token->encode($privateKey);

            http_response_code(201);
            $info['isLogin'] = true;
            $info['userInfo'] = ['id' => $idUser, 'email'=>$email,'accessToken' => $jwt];
            return $this->response->json_encode($headers);
        }
        return $this->response->json_encode($headers);
    }
}