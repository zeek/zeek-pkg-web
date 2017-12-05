<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 *
 * @method \App\Model\Entity\User[] paginate($object = null, array $settings = [])
 */
class UsersController extends AppController
{
    public $paginate = [
        'order' => [
            'Users.display_name' => 'asc'
        ]
    ];

    /**
     * Initialize method
     */
    public function initialize()
    {
        parent::initialize();
        $this->Auth->deny(['login', 'index', 'edit']);
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|void
     */
    public function index()
    {
        $users = $this->paginate($this->Users);

        $this->set(compact('users'));
        $this->set('_serialize', ['users']);
    }

    /**
     * Edit method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->Users->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }
        $this->set(compact('user'));
        $this->set('_serialize', ['user']);
    }


    public function login()
    {
        $session = $this->request->session();
        $redirecturl = $this->Auth->redirectUrl();
        if ($session->check('lastpage')) {
            $redirecturl = $session->consume('lastpage');
        }
        return $this->redirect($redirecturl);
    }

    public function logout()
    {
        $redir = $this->Auth->logout();
        $refer = $this->referer(true);
        if (preg_match('/users/', $refer)) {
            $redir = '/';
        }
        return $this->redirect($redir);
    }

    public function isAuthorized($user = null) 
    {
        $retval = false;  // Default deny

        // Allow login without already being logged in
        if ($this->request->action == 'login') {
            $retval = true;
        } elseif (!is_null($user)) {
            // Make sure user is admin user
            if (isset($user['admin']) && $user['admin']) {
                $retval = true;
            }
            // But prevent disabled users from doing anything
            if (isset($user['disabled']) && $user['disabled']) {
                $retval = false;
                $this->Auth->logout();
            } 
        }

        return $retval;
    }
}
