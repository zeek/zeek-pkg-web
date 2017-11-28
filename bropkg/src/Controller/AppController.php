<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Event\Event;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link https://book.cakephp.org/3.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('Security');`
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();

        $this->loadComponent('RequestHandler');
        $this->loadComponent('Flash');

        /*
         * Enable the following components for recommended CakePHP security settings.
         * see https://book.cakephp.org/3.0/en/controllers/components/security.html
         */
        $this->loadComponent('Security');
        $this->loadComponent('Csrf');

        $this->loadComponent('Auth', [
            'authenticate' => [
                'Muffin/OAuth2.OAuth',
            ]
        ]);

        // Set up Controller authorization via isAuthorized()
        $this->Auth->config('authorize', ['Controller']);

        // Allow all actions
        $this->Auth->allow();

    }

    /**
     * Before render callback.
     *
     * @param \Cake\Event\Event $event The beforeRender event.
     * @return \Cake\Http\Response|null|void
     */
    public function beforeRender(Event $event)
    {
        // Note: These defaults are just to get started quickly with development
        // and should not be used in production. You should instead set "_serialize"
        // in each action as required.
        if (!array_key_exists('_serialize', $this->viewVars) &&
            in_array($this->response->type(), ['application/json', 'application/xml'])
        ) {
            $this->set('_serialize', true);
        }


        // Get the user's id and display name if authenticated
        // and make available to the view
        $session = $this->request->session();
        $userId = $session->read('Auth.User.id');
        $userDisplayName = '';
        if (!is_null($userId)) {
            $userDisplayName = $session->read('Auth.User.display_name');
            if (strlen($userDisplayName) == 0) {
                $userDisplayName = 
                    $session->read('Auth.User.given_name') . ' ' .
                    $session->read('Auth.User.family_name');
            }
        }
        $this->set('userId', $userId);
        $this->set('userDisplayName', $userDisplayName);
    }

    /**
     * Check if use is authorized to view pages.
     *
     * @param array|\ArrayAccess $user Active user data
     * @return bool
     */
    public function isAuthorized($user = null) 
    {
        $retval = true;  // Default allow

        if (!is_null($user)) {
            // Prevent disabled users from doing anything
            if (isset($user['disabled']) && $user['disabled']) {
                $retval = false;
                $this->Auth->logout();
            } 
        }

        return $retval;
    }
}
