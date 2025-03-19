<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;

/**
 * Tags Controller
 *
 * @property \App\Model\Table\TagsTable $Tags
 *
 * @method \App\Model\Entity\Tag[] paginate($object = null, array $settings = [])
 */
class TagsController extends AppController
{
    public $paginate = [
        'order' => [
            'Tags.name' => 'asc'
        ]
    ];

    /**
     * Index method
     *
     * @return \Cake\Http\Response|void
     */
    public function index()
    {
        $tags = $this->paginate($this->Tags);

        $this->set(compact('tags'));
        $this->set('_serialize', ['tags']);
    }

    /**
     * View method
     *
     * @param string|null $id Tag id.
     * @return \Cake\Http\Response|void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        // If no tag specified, simply list all tags.
        if (is_null($id)) {
            return $this->redirect([
                'controller' => 'Tags', 
                'action' => 'index'
            ]);
        } 

        // If there's noise at the end of the URL path (i.e., anything after the
        // ID in "tags/view/<ID>"), redirect back to the view:
        $path = parse_url($this->request->url, PHP_URL_PATH);
        $parts = explode($id, $path);

        if (count($parts) >= 2 && strlen($parts[1]) > 0) {
            return $this->redirect([
                'controller' => 'Tags',
                'action' => 'view',
                $id
            ]);
        }

        $tag = $this->Tags->get($id, [
            'contain' => ['Metadatas']
        ]);

        /* Get list of associated package ids */
        $pkglist = array();
        foreach ($tag->metadatas as $metadatas) {
            $pkglist[$metadatas->package_id] = 1;
        }
        $pkgids = array_keys($pkglist);

        $packagesTable = TableRegistry::get('Packages');
        $packages = $this->paginate(
            $packagesTable
            ->find()
            ->where(['id IN' => $pkgids])
        );

        $this->set('tag', $tag);
        $this->set(compact('packages'));
        $this->set('_serialize', ['packages']);
    }
}
