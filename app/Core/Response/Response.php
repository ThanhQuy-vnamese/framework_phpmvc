<?php

namespace App\Core\Response;

use const App\Core\Helper\PREFIX_PUBLIC;

class Response
{
    private function setHeaderForAPI()
    {
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Credentials: true");
        header(
            "Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, X-Custom-Headers"
        );
    }

    public function setStatusCode(int $status)
    {
        http_response_code($status);
    }

    public function redirect(string $url, array $params = [])
    {
        $requestUrl = $_SERVER['REQUEST_URI'];
        $pos = strpos($requestUrl, PREFIX_PUBLIC);
        if ($pos !== false) {
            $pathPublic = substr($requestUrl, 0, $pos + strlen('/public'));
            $pathPublic = $pathPublic . $url;
        } else {
            $pathPublic = $url;
        }

        if (!empty($params)) {
            $linkParam = '';
            $lastItem = end($params);
            foreach ($params as $key => $value) {
                $linkParam .= $key . '=' . $value;
                if ($value !== $lastItem) {
                    $linkParam .= '&';
                }
            }
            $pathPublic .= '?' . $linkParam;
        }

        header('location:' . $pathPublic);
    }

    public function json_encode($data)
    {
        $this->setHeaderForAPI();
        return json_encode($data);
    }
}
