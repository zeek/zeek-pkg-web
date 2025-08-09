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
            'Packages.short_name' => 'asc'
        ]
    ];

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('Security');`
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Search.Search');
    }

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
        // If no package specified, simply list all packages.
        if (is_null($id)) {
            return $this->redirect([
                'controller' => 'Packages',
                'action' => 'index'
            ]);
        }

        // If there's noise at the end of the URL path (i.e., anything after the
        // ID in "packages/view/<ID>"), redirect back to the view:
        $path = parse_url($this->request->url, PHP_URL_PATH);
        $parts = explode($id, $path);

        if (count($parts) >= 2 && strlen($parts[1]) > 0) {
            return $this->redirect([
                'controller' => 'Packages',
                'action' => 'view',
                $id
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
