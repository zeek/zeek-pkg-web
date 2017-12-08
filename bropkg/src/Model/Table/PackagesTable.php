<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Packages Model
 *
 * @property \App\Model\Table\MetadatasTable|\Cake\ORM\Association\HasMany $Metadatas
 *
 * @method \App\Model\Entity\Package get($primaryKey, $options = [])
 * @method \App\Model\Entity\Package newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Package[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Package|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Package patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Package[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Package findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class PackagesTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('packages');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Search.Search');

        $this->hasMany('Metadatas', [
            'foreignKey' => 'package_id'
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->uuid('id')
            ->allowEmpty('id', 'create');

        $validator
            ->scalar('name')
            ->requirePresence('name', 'create')
            ->notEmpty('name');

        $validator
            ->scalar('url')
            ->allowEmpty('url');

        $validator
            ->scalar('readme')
            ->allowEmpty('readme');

        return $validator;
    }

    /**
     * https://github.com/FriendsOfCake/search
     */
    public function searchManager()
    {
        $searchManager = $this->behaviors()->Search->searchManager();
        $searchManager
            ->add('q', 'Search.Like', [
                'before' => true,
                'after' => true,
                'field' => [
                    'name', 'readme', 
                    'Metadatas.description', 'Metadatas.script_dir',
                    'Metadatas.plugin_dir', 'Metadatas.build_command',
                    'Metadatas.user_vars', ' Metadatas.test_command',
                    'Metadatas.config_files', 'Metadatas.depends',
                    'Metadatas.external_depends', 
                    'Metadatas.suggests',
                    'Tags.name'
                ]

            ]);

        return $searchManager;
    }
}
