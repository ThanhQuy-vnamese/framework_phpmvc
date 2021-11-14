<?php


namespace App\Model;


use App\Core\Model;

class LoginForm extends Model
{

    public string $email = '';
    public string $password = '';

    public function rules(): array
    {
        return [
            'email' => [self::RULE_REQUIRED, self::RULE_EMAIl],
            'password' => [self::RULE_REQUIRED]
        ];
    }
}