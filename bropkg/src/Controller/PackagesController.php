<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * Packages Controller
 *
 * @property \App\Model\Table\PackagesTable $Packages
 *
 * @method \App\Model\Entity\Package[] paginate($object = null, array $settings = [])
 */
class PackagesController extends AppController
{
    public $paginate = [
        'order' => [
            'Packages.name' => 'asc'
        ]
    ];

    // https://github.com/tanuck/cakephp-markdown
    public $helpers = [
        'Tanuck/Markdown.Markdown' => [
            'parser' => 'GithubMarkdown'
        ]
    ];

    /**
     * Index method
     *
     * @return \Cake\Http\Response|void
     */
    public function index()
    {
        $query = $this->Packages
            ->find('search', ['search' => $this->request->query])
            ->leftJoinWith('Metadatas')
            ->leftJoinWith('Metadatas.Tags')
            ->contain([
                'Metadatas' => ['sort' => ['Metadatas.version' => 'DESC']]
            ])
            ->group('Packages.id')
            ;

        $packages = $this->paginate($query);

        $this->set(compact('packages'));
        $this->set('_serialize', ['packages']);
    }

    /**
     * View method
     *
     * @param string|null $id Package id.
     * @return \Cake\Http\Response|void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        // If no pakcage specified, simply list all packages.
        if (is_null($id)) {
            return $this->redirect([
                'controller' => 'Packages',
                'action' => 'index'
            ]);
        }

        $packages = $this->Packages->find();
        $package = $this->Packages->get($id, [
            'contain' => ['Metadatas.Tags']
        ]);

        $this->set('package', $package);
        $this->set('packages', $packages);
        $this->set('_serialize', ['package']);
    }
}
