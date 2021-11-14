<?php

namespace App\Model;

use App\Core\Database\DBModel;

class User extends DBModel
{
    public const STATUS_INACTIVE = 0;
    public const STATUS_ACTIVE = 1;
    public const STATUS_DELETED = 2;

    public string $firstname = '';
    public string $lastname = '';
    public string $email = '';
    public int $status = self::STATUS_ACTIVE;
    public string $password = '';
    public string $passwordRepeat = '';

    public function rules(): array
    {
        return [
            'firstname' => [self::RULE_REQUIRED],
            'lastname' => [self::RULE_REQUIRED],
            'email' => [self::RULE_REQUIRED, self::RULE_EMAIl, [self::RULE_UNIQUE, 'class' => self::class]],
            'password' => [self::RULE_REQUIRED, [self::RULE_MIN, 'min' => 8], [self::RULE_MAX, 'max' => 16]],
            'passwordRepeat' => [self::RULE_REQUIRED, [self::RULE_MATCH, 'match' => 'password']],
        ];
    }

    public function register($email, $status, $password){
        $password = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO `covid_tb_users`(`email`, `status`, `password`) 
                VALUES ('$email', '$status', '$password')";
        return $this->getDatabase()->mysql->query($query);
    }
    public function register_face_google($email, $status, $token, $file)
    {
        $query = "INSERT INTO `covid_tb_users`(`email`, `status`,`token`, qr_image) 
        VALUES('$email', '$status', '$token', '$file')";
        return $this->getDatabase()->mysql->query($query);
    }
    public function add_profile($id_user, $firstname, $role, $lastname, $avatar=NULL,$birth=NULL, $gender=NULL)
    {
        $sql = "INSERT INTO `covid_tb_user_profiles`(`id_user`, `first_name`, `last_name`, `birthday`, `gender`, `avatar`, `role`)
                VALUES ($id_user, '$firstname', '$lastname', '$birth', '$gender', '$avatar', '$role')";
        return $this->getDatabase()->mysql->query($sql);
    }
    public function active_acc($token_res,$status, $email, $file){
        $sql= "UPDATE `covid_tb_users` SET `status`='$status',`token`='$token_res', qr_image='$file' WHERE email='$email'";
        return $this->getDatabase()->mysql->query($sql);
    }
    public function reset_pass($re_password, $email){
        $password = password_hash($re_password, PASSWORD_DEFAULT);
        $sql= "UPDATE `covid_tb_users` SET `password`='$password' WHERE email='$email'";
        return $this->getDatabase()->mysql->query($sql);
    }
    function tableName(): string
    {
        return 'covid_tb_users';
    }

    function attributes(): array
    {
        return ['id', 'password', 'email', 'status', 'qr_image'];
    }

    public function getPrimaryKey(): string
    {
        return 'id';
    }

    public function labels(): array
    {
        return [
            'email' => 'Email',
            'password' => 'Password',
            'passwordRepeat' => 'Password Repeat',
        ];
    }

    public function getUsers(): array {
        $row = $this->limitSelect();
        $query = "SELECT $row FROM covid_tb_users";
        $result = $this->getDatabase()->mysql->query($query);
        $numRows = $result->num_rows;
        if ($numRows > 0) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }
    public function getInfo($id){
        $sql = "SELECT * FROM covid_tb_users u inner join covid_tb_user_profiles up on u.id=up.id_user WHERE up.id_user=$id";
        $result = $this->getDatabase()->mysql->query($sql);
        $info = [];
        if($result){
            if($result->num_rows >0){
                while ($row=$result->fetch_array()){
                    $info['email'] = $row['email'];
                    $info['first_name'] = $row['first_name'];
                    $info['last_name'] = $row['last_name'];
                    $info['birthday'] = $row['birthday'];
                    $info['gender'] = $row['gender'];
                    $info['avatar'] = $row['avatar'];
                }
            }
        }
        return $info;
    }
}
