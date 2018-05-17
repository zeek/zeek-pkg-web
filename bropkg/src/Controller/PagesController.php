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

use Cake\Core\Configure;
use Cake\Network\Exception\ForbiddenException;
use Cake\Network\Exception\NotFoundException;
use Cake\View\Exception\MissingTemplateException;

/**
 * Static content controller
 *
 * This controller will render views from Template/Pages/
 *
 * @link https://book.cakephp.org/3.0/en/controllers/pages-controller.html
 */
class PagesController extends AppController
{

    /**
     * Displays a view
     *
     * @param array ...$path Path segments.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Network\Exception\ForbiddenException When a directory traversal attempt.
     * @throws \Cake\Network\Exception\NotFoundException When the view file could not
     *   be found or \Cake\View\Exception\MissingTemplateException in debug mode.
     */
    public function display(...$path)
    {
        $count = count($path);
        if (!$count) {
            return $this->redirect('/');
        }
        if (in_array('..', $path, true) || in_array('.', $path, true)) {
            throw new ForbiddenException();
        }
        $page = $subpage = null;

        if (!empty($path[0])) {
            $page = $path[0];
        }
        if (!empty($path[1])) {
            $subpage = $path[1];
        }
        $this->set(compact('page', 'subpage'));

        /* Show Top 5 Watched and Last 5 Updated on home page */
        $this->loadModel('Packages');
        $packagecount = $this->Packages->find('all')->all()->count();
        $this->set('packagecount', $packagecount);
        $query = $this->Packages
            ->find('all')
            ->select(['id', 'name', 'short_name', 'subscribers_count' ])
            ->order(['Packages.subscribers_count' => 'desc'])
            ->limit(5);
        $this->set('topwatched', $query->all());
        $query = $this->Packages
            ->find('all')
            ->select(['id', 'name', 'short_name', 'stargazers_count' ])
            ->order(['Packages.stargazers_count' => 'desc'])
            ->limit(5);
        $this->set('topstarred', $query->all());
        $query = $this->Packages
            ->find('all')
            ->select(['id', 'name', 'short_name', 'pushed_at' ])
            ->order(['Packages.pushed_at' => 'desc'])
            ->limit(5);
        $this->set('lastupdated', $query->all());


        try {
            $this->render(implode('/', $path));
        } catch (MissingTemplateException $exception) {
            if (Configure::read('debug')) {
                throw $exception;
            }
            throw new NotFoundException();
        }

    }
}
