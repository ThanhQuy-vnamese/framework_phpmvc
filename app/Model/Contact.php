<?php

namespace App\Model;

use App\Core\Database\DBModel;

class Contact extends DBModel
{
public function rules(): array
{
    // TODO: Implement rules() method.
}
public function insert_contact($id_user, $email, $message)
{
    if($id_user!='') {

        $sql = "INSERT INTO `covid_tb_contact_infomations` (`id_user`, `email`, `message`) 
    VALUES ($id_user,'$email','$message')";
    }
    else{
        $sql = "INSERT INTO `covid_tb_contact_infomations` (`email`, `message`) 
    VALUES ('$email','$message')";
    }
    return $this->getDatabase()->mysql->query($sql);

}
}