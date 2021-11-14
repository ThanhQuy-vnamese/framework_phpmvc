<?php

namespace App\Model;

use App\Core\Application;
use App\Core\Database\DBModel;

class Medican extends DBModel{

    function tableName(): string
    {
        // TODO: Implement tableName() method.
    }

    function attributes(): array
    {
        // TODO: Implement attributes() method.
    }

    public function rules(): array
    {
        // TODO: Implement rules() method.
    }

    public function getInfo($id){
        $sql = "SELECT * FROM covid_tb_medican_records mc inner join covid_tb_users u on u.id = mc.id_user 
    inner join covid_tb_user_profiles up on u.id=up.id_user WHERE mc.id_user=$id";
//        var_dump($sql); die();
        $result = $this->getDatabase()->mysql->query($sql);
        $info = [];
        if($result){
            if($result->num_rows >0){
                while ($row=$result->fetch_array()){
                    $info['email'] = $row['email'];
                    $info['full_name'] = $row['full_name'];
                    $info['phone'] = $row['phone'];
                    $info['address'] = $row['address'];
                    $info['birthday'] = $row['birthday'];
                    $info['health_insurance'] = $row['health_insurance'];
                    $info['gender'] = $row['gender'];
                    $info['healths'] = unserialize($row['healths']);
                    $info['way'] = $row['way'];
                }
            }
        }
        return $info;
    }

    public function InsertRecord($email, $id_user, $fullname, $phone, $address, $birthday, $health_insurance, $gender, $health, $way){
        $sql= "INSERT INTO `covid_tb_medican_records`(`id_user`, `address`, `phone`, `full_name`, `way`, `healths`, `health_insurance`) 
VALUES ($id_user,'$address','$phone','$fullname','$way','$health','$health_insurance')";
        return $this->getDatabase()->mysql->query($sql);
    }

    public function UpdateRecord($email, $id_user, $fullname, $phone, $address, $birthday, $health_insurance, $gender, $health, $way){
        $sql= "UPDATE `covid_tb_medican_records` SET `full_name`='$fullname',`phone`='$phone',
                     `address`='$address',`health_insurance`='$health_insurance',`healths`='$health',`way`='$way' WHERE id_user=$id_user";
        return $this->getDatabase()->mysql->query($sql);
    }
    public function Download($filename){
        $filePath = Application::$ROOT_DIR.'/public/upload/'.$filename;
        if(!empty($filename) && file_exists($filePath)){
            // Define headers
            header('Content-Length: ' . filesize($filePath));
            header('Content-Encoding: none');
            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            header("Content-Disposition: attachment; filename=$filename");
            header("Content-Type: application/zip");
            header("Content-Transfer-Encoding: binary");

            // Read the file
            readfile($filePath);
            exit;
        }else{
            echo 'The File '.$filename.' does not exist.';
        }
    }
}