<?php

declare(strict_types=1);

namespace Application\Controller;

use Application\AppAuthAdapter;
use Laminas\Session;
use Laminas\Authentication\AuthenticationServiceInterface;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class AuthController extends AbstractActionController
{

    /**
     * @var AuthenticationServiceInterface
     */
    private $auth;
    /**
     * @var AppAuthAdapter
     */
    private $adapter;

    public function __construct(AuthenticationServiceInterface $auth, AppAuthAdapter $adapter) {

        $this->auth = $auth;
        $this->adapter = $adapter;
        $this->session = new Session\Container();
    }
    public function indexAction()
    {
        $identity = $this->identity();

        if (!$identity) {
            return $this->redirect()->toRoute('application', ['controller'=>'auth', 'action'=>'login'], [], true);
        }

        $prevUrl = $this->params()->fromQuery('prevUrl', null);
        if ($prevUrl) {
            return $this->redirect()->toUrl($prevUrl);
        }

        // Default controll panel page
        return $this->redirect()->toRoute('main', ['controller'=>'index','action'=>'index'], [], true);
    }


    public function LoginAction() {
        $prevUrl = $this->params()->fromQuery('prevurl', false);
        $identity = $this->identity();
        if ($identity) {
            if ($prevUrl) {
                return $this->redirect()->toUrl($prevUrl);
            }

            return $this->redirect()->toRoute('main', ['controller'=>'index','action'=>'index'], [], true);
        }

        $viewData = [];
        $postedData = $this->params()->fromPost();
        if (empty($postedData)) {
            // Not posted
            if (!empty($this->session['auth'])) {
                $viewData['username'] = $this->session['auth']['username'];
            }
            return new ViewModel($viewData);
        }

        // Posted
        $this->adapter->setCredentials($postedData['username'], $postedData['password']);
        $result = $this->auth->authenticate($this->adapter);
        if ($result->isValid()) {
            if ($prevUrl) {
                return $this->redirect()->toUrl($prevUrl);
            }
            return $this->redirect()->toRoute('main', ['controller'=>'index','action'=>'index'], [], true);
        }
        // Failed atempt at login
        $this->session['auth'] = [
            'username' => $postedData['username']
        ];

        if (!array_key_exists('trys', $this->session['auth'])) {
            $this->session['auth']['trys'] = 1;
        } else {
            $this->session['auth']['trys']++;
        }

        $this->flashMessenger()->addSuccessMessage('Невалидно потребителско име или парола');
        return $this->redirect()->toRoute('application', ['controller'=>'auth','action'=>'login'], [], true);
    }
    public function LogoutAction() {
        $this->auth->clearIdentity();
        return $this->redirect()->toRoute('application', ['controller'=>'auth','action'=>'login'], [], true);
    }
}
