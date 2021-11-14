<?php

namespace App\Core\Helper;

const PREFIX_PUBLIC = '/public';

class Helper
{
    function custom_link(string $path): string
    {
        $requestUrl = $_SERVER['REQUEST_URI'];
        $pos = strpos($requestUrl, PREFIX_PUBLIC);
        if (!$pos) {
            $pathPublic = '/public';
        } else {
            $pathPublic = substr($requestUrl, 0, $pos + strlen('/public'));
        }
        $http = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        return $http . $host . $pathPublic . '/' . $path;
    }

    /**
     * @param string $path
     * @param array $params
     * @return string|void
     */
    public function redirect(string $path, array $params = []): string
    {
        $requestUrl = $_SERVER['REQUEST_URI'];
        $pos = strpos($requestUrl, PREFIX_PUBLIC);
        if (!$pos) {
            $pathPublic = '/public' . $path;;
        } else {
            $pathPublic = substr($requestUrl, 0, $pos + strlen('/public'));
            $pathPublic = $pathPublic . $path;
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

        return $pathPublic;
    }
}
