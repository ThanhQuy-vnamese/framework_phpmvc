<?php
namespace App\Core\Request;

class Request {
    public Input $input;

    public function __construct()
    {
        $this->input = new Input($this->getAllInput());
    }

    public function getPath() {
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        $position = strpos($path, '?');
        if ($position === false) {
            return $path;
        }
        return substr($path, 0, $position);
    }

    public function getHeaders(): array {
        $headers = array();
        foreach($_SERVER as $key => $value) {
            if (substr($key, 0, 5) !== 'HTTP_') {
                continue;
            }
            $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
            $headers[$header] = $value;
        }
        return $headers;
    }

    public function getMethod(): string {
        return strtolower($_SERVER['REQUEST_METHOD'] ?? '');
    }

    public function isGet(): bool {
        return $this->getMethod() === 'get';
    }

    public function isPost(): bool {
        return $this->getMethod() === 'post';
    }

    public function getAllInput(): array {
        $input = [];
        if ($this->isGet()) {
            foreach ($_GET as $key => $value) {
                $input[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }

        if ($this->isPost()) {
            foreach ($_POST as $key => $value) {
                if(is_array($value)) {
                    $input[$key] = $value;
                } else {
                    $input[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
                }
            }

            $paramsGetContent = (array)json_decode(file_get_contents('php://input'), true);
            if (!is_null($paramsGetContent)) {
                foreach ($paramsGetContent as $key => $value) {
                    $input[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
                }
            }
        }

        return $input;
    }
}
