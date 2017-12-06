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
        $packages = $this->paginate($this->Packages);

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
        $package = $this->Packages->get($id, [
            'contain' => ['Metadatas.Tags']
        ]);

        $this->set('package', $package);
        $this->set('_serialize', ['package']);
    }
}
