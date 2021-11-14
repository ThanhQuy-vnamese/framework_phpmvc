<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth\Authentication;
use App\Core\Controller\BaseController;
use App\Core\Session;
use App\Model\Contact;

class ContactController extends BaseController
{
    public function insert_contact()
    {
        $session = new Session();
        if ($this->request->isPost()) {
            $contact = new Contact();
            $authentication = new Authentication();
            $email = $this->request->input->get('email');
            $message = $this->request->input->get('message');
            $id_user = $authentication->user()->id;
            $isContact = $contact->insert_contact($id_user, $email, $message);
            if ($isContact) {
                $session->setFlash('message', 'Success!');
                $this->response->redirect('/');
            } else {
                $session->setFlash('message', 'Something went wrong! Try later!');
            }
        } else {
            $session->setFlash('message', 'Something went wrong! Try later!');
        }
    }
}
