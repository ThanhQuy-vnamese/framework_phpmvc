<?php

namespace App\Controllers;

use App\Core\Application;
use App\Core\Auth\Authentication;
use App\Core\Controller\BaseController;
use App\Core\Database\Query;
use App\Core\Helper\Helper;
use App\Core\Lib\Token;
use App\Core\Session;
use App\Model\User;
use Facebook\Facebook;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Label\Alignment\LabelAlignmentCenter;
use Endroid\QrCode\Label\Font\NotoSans;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;

class AuthController extends BaseController
{

    public function login()
    {
        if ($this->request->isPost())
        {
            $email = $this->request->input->get('email');
            $password = $this->request->input->get('password');
            $remember = $this->request->input->get('remember');
            $isRemember = !empty($remember);
            $authentication = new Authentication();
            $isLogin = $authentication->login(['email' => $email, 'password' => $password], $isRemember);
            $session = new Session();
            if ($isLogin) {
                $this->response->redirect('/');
            } else {
                $session->setFlash('warning_message', 'Your email or password is wrong!');
                $this->response->redirect('/login');
            }
        }
        $fb = new Facebook([
            'app_id' => '1287390858369696',
            'app_secret' => '8bac20cf62685585675fb12b2dbb449b',
            'default_graph_version' => 'v12.0',
        ]);
        $helper = $fb->getRedirectLoginHelper();

        $permissions = ['email']; // Optional permissions
        $loginUrl = $helper->getLoginUrl('https://thanhquy.top/public/validate_facebook', $permissions);
        return $this->twig->render('pages/login', ['loginFace'=>$loginUrl]);
    }

    public function logout()
    {
        $authentication = new Authentication();
        $authentication->logout();
        $this->response->redirect('/');
    }

    public function register()
    {
        return $this->twig->render('pages/register');
    }

    public function validate_facebook()
    {
        //session_start();
        if($this->request->isGet())
        {
            $facebook = new Facebook([
                'app_id' => '1287390858369696',
                'app_secret' => '8bac20cf62685585675fb12b2dbb449b',
                'default_graph_version' => 'v12.0',
            ]);
            $session = new Session();
            $authentication = new Authentication();
            $authentication->setRememberPassword(true);

            $facebook_helper = $facebook->getRedirectLoginHelper();

            if(isset($_GET['code']))
            {
                $access_token = $facebook_helper->getAccessToken();
                $facebook->setDefaultAccessToken($access_token);

                // Lấy giá trị của token
                $getValueToken = $access_token->getValue();

                // Kiểm tra nếu tồn tại email và status = 1 thì chuyển về trang chủ
                $grap_response = $facebook->get("/me?fields=name, email", $access_token);
                $facebook_user_info = $grap_response->getGraphUser();
                $user_email = $facebook_user_info['email'];

                $user = new User();
                $sql = "SELECT * FROM covid_tb_users WHERE email='$user_email' and status='1'";
                $result = $user->getDatabase()->mysql->query($sql);

                if(mysqli_num_rows($result)>0)
                {
                    while($row_users = mysqli_fetch_array($result))
                    {
                        $id_user = $row_users['id'];
                    }

                    $sql_profile =  "SELECT * FROM covid_tb_user_profiles WHERE id_user='$id_user'";
                    $result_profile =  $user->getDatabase()->mysql->query($sql_profile);

                    while($row_profiles = mysqli_fetch_array($result_profile))
                    {
                        $first_name = $row_profiles['first_name'];
                        $last_name = $row_profiles['last_name'];
                    }

                    // Chuẩn bị users để xác thực authentication
                    $users = [
                        'id' => $id_user,
                        'firstname' => $first_name,
                        'lastname' => $last_name,
                        'email'=> $user_email,
                    ];
                    $authentication->setUserInfo($users);
                    $this->response->redirect('/');
                }
                else
                {
                    // Chuẩn bị các data để đăng ký tài khoản
                    if(!empty($facebook_user_info))
                    {
                        $avatar = 'http://graph.facebook.com/'.$facebook_user_info['id'].'/picture';
                    }
                    if(!empty($facebook_user_info['name']))
                    {
                        $user_name =  explode(" ",$facebook_user_info['name']);
                        $first_name = array_shift($user_name);
                        $last_name = array_pop($user_name);
                    }

                    // Set status và role của users
                    $status = 1;
                    $role = 1;

                    $helper = new Helper();
                    $link = $helper->custom_link('yourrecord');
                    $full_link = $link . '?token=' . $getValueToken;
                    $qr = Builder::create()
                        ->writer(new PngWriter())
                        ->writerOptions([])
                        ->data("$full_link")
                        ->encoding(new Encoding('UTF-8'))
                        ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
                        ->size(300)
                        ->margin(10)
                        ->roundBlockSizeMode(new RoundBlockSizeModeMargin())
                        ->labelText('Download Your QR')
                        ->labelFont(new NotoSans(20))
                        ->labelAlignment(new LabelAlignmentCenter())
                        ->build();
                    function getUsernameFromEmail($user_email)
                    {
                        $find = '@';
                        $pos = strpos($user_email, $find);
                        $username = substr($user_email, 0, $pos);
                        return $username;
                    }

                    $filename = getUsernameFromEmail($user_email);
                    $file = $filename . '.png';
                    $qr->saveToFile(Application::$ROOT_DIR . '/public/upload/' . $file);

                    // $isRegister = $user->register_face_google($user_email, $status, $getValueToken);
                    // Thiết lập đăng ký tài khoản
                    $isRegister = $user->register_face_google($user_email, $status, $getValueToken, $file);

                    if(!$isRegister)
                    {
                        echo("Đăng ký không thành công! ");
                    }

                    // Lấy id_user vừa mới register xong để insert vào profile
                    $result_2 = $user->getDatabase()->mysql->query($sql);
                    if($result_2)
                    {
                        while($row = mysqli_fetch_array($result_2))
                        {
                            $id_user = $row['id'];
                        }
                        $user->add_profile($id_user, $first_name, $role, $last_name, $avatar);
                        $users = [
                            'id' => $id_user,
                            'firstname' => $first_name,
                            'lastname' => $last_name,
                            'email'=> $user_email,
                        ];
                        $authentication->setUserInfo($users);
                        $this->response->redirect('/');
                    }
                }
            }
        }
    }
    public function newRegister()
    {
        $session = new Session();
        if ($this->request->isPost()) {
            $user = new User();
            $email = $this->request->input->get('email');
            $firstname = $this->request->input->get('firstname');
            $lastname = $this->request->input->get('lastname');
            $password = $this->request->input->get('password');
            $gender = $this->request->input->get('gender');
            $birth = $this->request->input->get('birth');
            $status = 0;
            $role = 1;
            $isRegister = $user->register($email, $status, $password);

            if (!$isRegister) {
                $session->setFlash('warning_message', 'Email already exist! Please enter another one.');
                $this->response->redirect('/register');
            }

            $id_user = $this->get_id_user();
            $avatar = '';
            $isAddprofile = $user->add_profile($id_user, $firstname, $role, $lastname, $birth, $avatar,$gender);

            if (!$isAddprofile) {
                $session->setFlash('warning_message', 'Something went wrong');
                $this->response->redirect('/register');
            }

            $token = new Token();
            $payload = ['email' => $email, 'role' => $role];
            $key = 'abc';
            $token->setPayload($payload);
            $a = $token->encode($key);
            $helper = new Helper();
            $link = $helper->custom_link('verify');
            $full_link = $link . '?token=' . $a;
            $subject = 'ACTIVE YOUR ACCOUNT';
            $body = 'click here to active your account <a href="' . $full_link . '">' . $full_link . '</a>';
            $this->send_mail($email, $session, $subject, $body, $firstname);
        }
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function profile(): string
    {
        $new_user = new User();
        $user = $new_user->get();
        foreach ($user as $us) {
            echo $us->email;
        }
    }

    public function get_id_user(): string
    {
        $user = new User();
        $new_user = $user->get();
        foreach ($new_user as $us) {
            $id_user = $us->id;
        }
        return $id_user;
    }

    public function getProfile()
    {
        $authentication = new Authentication();
        $user_info = new User();
        $info = $user_info->getInfo($authentication->user()->id);
        $is_exist_info = !empty($info);
        $email = $info['email'] ?? '';
        $first_name = $info['first_name'] ?? '';
        $last_name = $info['last_name'] ?? '';
        $birthday = $info['birthday'] ?? '';
        $gender = $info['gender'] ?? '';
        $avatar = empty($info['avatar']) ? 'default.png':$info['avatar'] ;
        $avatar_link = 'upload/avatar/'.$avatar;
        return $this->twig->render('pages/profile', ['email' => $email,'birthday' => $birthday, 'firstname' => $first_name,
            'lastname' => $last_name, 'gender' => $gender, 'avatar_link' => $avatar_link, 'is_exist_info' => $is_exist_info]);
    }

    public function profile_edit_view()
    {
        $authentication = new Authentication();
        $user_info = new User();
        $info = $user_info->getInfo($authentication->user()->id);
        $is_exist_info = !empty($info);
        $email = $info['email'] ?? '';
        $first_name = $info['first_name'] ?? '';
        $last_name = $info['last_name'] ?? '';
        $birthday = $info['birthday'] ?? '';
        $gender = $info['gender'] ?? '';
        $avatar = $info['avatar'] ?? '';
        return $this->twig->render('pages/profile_edit', ['email' => $email,'birthday' => $birthday, 'firstname' => $first_name,
            'lastname' => $last_name, 'gender' => $gender, 'avatar' => $avatar, 'is_exist_info' => $is_exist_info]);
    }

    public function profile_edit()
    {
        if ($this->request->isPost()) {
            $user = new User();
            $authentication = new Authentication();
            $firstname = $this->request->input->get('firstname');
            $lastname = $this->request->input->get('lastname');
            $birthday = $this->request->input->get('birthday');
            $gender = $this->request->input->get('gender');
            $uploads_dir = 'upload/avatar';
            $tmp_name = $_FILES["avatar"]["tmp_name"];
            $session = new Session();
            if(empty($tmp_name)){
                $data_update = ['first_name'=>$firstname, 'last_name'=>$lastname, 'birthday'=>$birthday, 'gender'=>$gender];
            }
            else{
                $type_file = $_FILES["avatar"]["type"];
                if($type_file!='image/jpeg' || $type_file!='image/png' || $type_file!='image/jpg'){
                    $session->setFlash('warning_message', 'The file is invalid!');
                    $this->response->redirect('/edit_profile');
                }else {
                    $avatar = basename($_FILES["avatar"]["name"]);
                    move_uploaded_file($tmp_name, "$uploads_dir/$avatar");
                    $data_update = ['first_name' => $firstname, 'last_name' => $lastname, 'birthday' => $birthday, 'gender' => $gender, 'avatar' => $avatar];
                }
            }
            $query = new Query();
            $profile_update = $query->table('covid_tb_user_profiles')
                ->update($data_update, ['id_user'=>$authentication->user()->id]);
            if($profile_update){
                $session->setFlash('message', 'Your profile update successfully!');
                $this->response->redirect('/profile');
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
        $role = $payload->role;
        $status = 1;
        $query = "SELECT * FROM covid_tb_users where email like '$email'";
        $result = $user->getDatabase()->mysql->query($query);
        if ($result) {
            $helper = new Helper();
            $link = $helper->custom_link('yourrecord');
            $full_link = $link . '?token=' . $token_res;
            $qr = $this->createQRCode($full_link);

            $filename = $this->getUsernameFromEmail($email);
            $file = $filename . '.png';
            $qr->saveToFile(Application::$ROOT_DIR . '/public/upload/' . $file);
            $result2 = $user->active_acc($token_res, $status, $email, $file);
            $session = new Session();
            if(!$result2) {
                $session->setFlash('message', 'Something went wrong! Try later!');
                $this->response->redirect('/register');
            }
            $session->setFlash('message', 'Your account have been active success!');
            $this->response->redirect('/login');
        }
    }

    function getUsernameFromEmail($email)
    {
        $find = '@';
        $pos = strpos($email, $find);
        $username = substr($email, 0, $pos);
        return $username;
    }
    public function sendmail_reset()
    {
        $session = new Session();
        if ($this->request->isPost()) {
            $user = new User();
            $email = $this->request->input->get('email');
            $token = new Token();
            $payload = ['email' => $email];
            $key = 'abc';
            $token->setPayload($payload);
            $encode = $token->encode($key);
            $helper = new Helper();
            $link = $helper->custom_link('resetpass');
            $full_link = $link . '?token=' . $encode;
            $subject = 'RESET PASSWORD YOUR ACCOUNT';
            $body = 'click here to reset password your account <a href="' . $full_link . '">' . $full_link . '</a>';
            $this->send_mail($email, $session, $subject, $body);
        }
    }

    public function resetPassword()
    {
        if ($this->request->isPost()) {
            $user = new User();
            $new_pass = $this->request->input->get('new_password');
            $re_pass = $this->request->input->get('password');
            $email = $this->request->input->get('email');
            $old_pass = $this->request->input->get('old_password');
            $session = new Session();
            $sql = "SELECT * FROM covid_tb_users WHERE email='$email'";
            $result = $user->getDatabase()->mysql->query($sql);

            if ($result) {
                $result2 = $user->reset_pass($re_pass, $email);
                if ($result2) {
                    $session->setFlash('message', 'Your account have been change password success!');
                    $this->response->redirect('/login');
                } else {
                    $session->setFlash('warning_message', 'Something went wrong! Try later!');
                }
            }
        }
    }

    public function sendmail()
    {
        return $this->twig->render('pages/sendmail_resetpass');
    }

    public function reset_password()
    {
        $token_res = $this->request->input->get('token');
        $token = new Token();
        $key = 'abc';
        $payload = $token->decode($token_res, $key);
        $email = $payload->email;
        return $this->twig->render('pages/reset_password', ['email' => $email]);
    }

    public function send_mail(string $email, Session $session, string $subject, string $body, string $firstname = ''): void
    {
        $mail = new PHPMailer(true);
        try {
            $mail->SMTPDebug = SMTP::DEBUG_OFF;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host = 'smtp.gmail.com';                     //Set the SMTP server to send through
            $mail->SMTPAuth = true;                                   //Enable SMTP authentication
            $mail->Username = 'tranthanhquy19081206@gmail.com';                     //SMTP username
            $mail->Password = '18037231quymh@';                               //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;            //Enable implicit TLS encryption
            $mail->Port = 587;                               //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
            $mail->setFrom('tranthanhquy19081206@gmail.com', 'Mailer');
            if (empty($firstname)) {
                $mail->addAddress("$email");
            } else {
                $mail->addAddress("$email", "$firstname");     //Add a recipient
            }

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = $subject;
//            $mail->Subject = 'ACTIVE YOUR ACCOUNT';
//            $mail->Body = 'click here to active your account <a href="' . $full_link . '">' . $full_link . '</a>';
            $mail->Body = $body;
            $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $mail->send();
            $session->setFlash('message', 'Message has been sent! Check your email');
            $this->response->redirect('/login');
        } catch (Exception $e) {
            $session->setFlash('warning_message', "Something went wrong. Try Later!'.$e.'");
        }
    }

    /**
     * @param string $full_link
     * @return \Endroid\QrCode\Writer\Result\ResultInterface
     */
    public function createQRCode(string $full_link): \Endroid\QrCode\Writer\Result\ResultInterface
    {
        $qr = Builder::create()
            ->writer(new PngWriter())
            ->writerOptions([])
            ->data("$full_link")
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->size(300)
            ->margin(10)
            ->roundBlockSizeMode(new RoundBlockSizeModeMargin())
            ->labelText('Download Your QR')
            ->labelFont(new NotoSans(20))
            ->labelAlignment(new LabelAlignmentCenter())
            ->build();
        return $qr;
    }
}
