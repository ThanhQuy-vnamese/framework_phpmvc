<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Application;
use App\Core\Auth\Authentication;
use App\Core\Controller\BaseController;
use App\Core\Helper\Helper;
use App\Core\Lib\Token;
use App\Core\Middleware\AuthMiddleware;
use App\Core\Session;
use App\Model\Medican;
use App\Model\User;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Label\Alignment\LabelAlignmentCenter;
use Endroid\QrCode\Label\Font\NotoSans;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;

class MedicanRecordController extends BaseController
{
    public function newForm()
    {
        $authentication = new Authentication();
        $qr_image = $authentication->user()->qr_image;
        $medican = new Medican();
        $info = $medican->getInfo($authentication->user()->id);
        $is_exist_info = !empty($info);
        $email = $info['email'] ?? '';
        $full_name = $info['full_name'] ?? '';
        $phone = $info['phone'] ?? '';
        $address = $info['address'] ?? '';
        $birthday = $info['birthday'] ?? '';
        $health_insurance = $info['health_insurance'] ?? '';
        $gender = $info['gender'] ?? '';
        $health = $info['healths'] ?? '';
        $way = $info['way'] ?? '';
        $link = '/download?qr_code=' . $qr_image;
        return $this->twig->render('pages/medican_record', ['qr_image' => $qr_image, 'email' => $email, 'full_name' => $full_name,
            'phone' => $phone, 'address' => $address, 'birthday' => $birthday, 'health_insurance' => $health_insurance, 'gender' => $gender, 'health' => $health,
            'way' => $way, 'link' => $link, 'is_exist_info' => $is_exist_info]);
    }

    public function updateForm()
    {
        $session = new Session();
        if ($this->request->isPost()) {
            $authentication = new Authentication();
            $record = new Medican();
            $email = $this->request->input->get('email');
            $full_name = $this->request->input->get('full_name');
            $phone = $this->request->input->get('phone');
            $address = $this->request->input->get('address');
            $birthday = $this->request->input->get('birthday');
            $health_insurance = $this->request->input->get('health_insurance');
            $gender = $this->request->input->get('gender');
            $healths = $this->request->input->get('healths');
            $health_serializer = serialize($healths);
            $way = $this->request->input->get('way');
            $id_user = $authentication->user()->id;
            $updateRecord = $record->UpdateRecord($email, $id_user, $full_name, $phone, $address, $birthday, $health_insurance, $gender, $health_serializer, $way);
            if ($updateRecord) {
                $session->setFlash('message', 'Success!');
                $this->response->redirect('/services');
            } else {
                $session->setFlash('message', 'Something went wrong! Try later!');
            }
        } else {
            $session->setFlash('message', 'Something went wrong! Try later!');
        }
    }

    public function insertForm()
    {
        $session = new Session();
        if ($this->request->isPost()) {
            $authentication = new Authentication();
            $record = new Medican();
            $email = $this->request->input->get('email');
            $full_name = $this->request->input->get('full_name');
            $phone = $this->request->input->get('phone');
            $address = $this->request->input->get('address');
            $birthday = $this->request->input->get('birthday');
            $health_insurance = $this->request->input->get('health_insurance');
            $gender = $this->request->input->get('gender');
            $healths = $this->request->input->get('healths');
            $health_serializer = serialize($healths);
            $way = $this->request->input->get('way');
            $id_user = $authentication->user()->id;
            $insertRecord = $record->InsertRecord($email, $id_user, $full_name, $phone, $address, $birthday, $health_insurance, $gender, $health_serializer, $way);
            if ($insertRecord) {
                $session->setFlash('message', 'Success!');
                $this->response->redirect('/services');
            } else {
                $session->setFlash('message', 'Something went wrong! Try later!');
            }
        } else {
            $session->setFlash('message', 'Something went wrong! Try later!');
        }
    }

    public function downloadQR()
    {
        $record = new Medican();
        $filename = $this->request->input->get('qr_code');
        $session = new Session();
        if (!empty($filename)) {
            $result = $record->Download($filename);
            if ($result) {
                $session->setFlash('message', 'Success!');
                $this->response->redirect('/services');
            } else {
                $session->setFlash('message', 'Something went wrong! Try later!');
            }
        }
    }

    public function verifyAccount()
    {
        $user = new User();
        $token_res = $this->request->input->get('token');
        $token = new Token();
        $key = 'abc';
        $payload = $token->decode($token_res, $key);
        $email = $payload->email;
        $query = "SELECT * FROM covid_tb_users where email like '$email'";
        $result = $user->getDatabase()->mysql->query($query);
        $session = new Session();
        if ($result) {
            while ($row = mysqli_fetch_array($result)) {
                $password = $row['password'];
                $email = $row['email'];
                $authentication = new Authentication();
                $login = $authentication->login(['email' => $email, 'password' => $password]);
                if ($login) {
                    $session->setFlash('message', 'Success!');
                    $this->response->redirect('/');
                }
            }
        } else {
            $session->setFlash('message', 'Try Later!');
        }
    }

    public function yourRecord()
    {
        $user = new User();
        $token_res = $this->request->input->get('token');
        $token = new Token();
        $key = 'abc';
        $payload = $token->decode($token_res, $key);
        $email = $payload->email;
        $query = "SELECT * FROM covid_tb_users where email like '$email'";
        $result = $user->getDatabase()->mysql->query($query);
        if ($result) {
            while ($row = mysqli_fetch_array($result)) {
                $password = $row['password'];
                $email = $row['email'];
                $authentication = new Authentication();
                $login = $authentication->login(['email' => $email, 'password' => $password]);
                if ($login) {
                    $qr_image = $authentication->user()->qr_image;
                    $medican = new Medican();
                    $info = $medican->getInfo($authentication->user()->id);
                    $is_exist_info = !empty($info);
                    $email = $info['email'] ?? '';
                    $full_name = $info['full_name'] ?? '';
                    $phone = $info['phone'] ?? '';
                    $address = $info['address'] ?? '';
                    $birthday = $info['birthday'] ?? '';
                    $health_insurance = $info['health_insurance'] ?? '';
                    $gender = $info['gender'] ?? '';
                    $health = $info['healths'] ?? '';
                    $way = $info['way'] ?? '';
                    $link = '/download?qr_code=' . $qr_image;
                    return $this->twig->render('pages/medican_record', ['qr_image' => $qr_image, 'email' => $email, 'full_name' => $full_name,
                        'phone' => $phone, 'address' => $address, 'birthday' => $birthday, 'health_insurance' => $health_insurance, 'gender' => $gender, 'health' => $health,
                        'way' => $way, 'link' => $link, 'is_exist_info' => $is_exist_info]);
                }
            }
        } else {
            echo "<script>alert('Something went wrong! Try later!');</script>";
        }
    }
}
