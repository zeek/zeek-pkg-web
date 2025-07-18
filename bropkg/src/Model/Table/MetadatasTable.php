<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Metadatas Model
 *
 * @property \App\Model\Table\PackagesTable|\Cake\ORM\Association\BelongsTo $Packages
 * @property \App\Model\Table\TagsTable|\Cake\ORM\Association\BelongsToMany $Tags
 *
 * @method \App\Model\Entity\Metadata get($primaryKey, $options = [])
 * @method \App\Model\Entity\Metadata newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Metadata[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Metadata|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Metadata patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Metadata[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Metadata findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class MetadatasTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('metadatas');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Packages', [
            'foreignKey' => 'package_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsToMany('Tags', [
            'foreignKey' => 'metadata_id',
            'targetForeignKey' => 'tag_id',
            'joinTable' => 'metadatas_tags'
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): \Cake\Validation\Validator
    {
        $validator
            ->uuid('id')
            ->allowEmpty('id', 'create');

        $validator
            ->scalar('version')
            ->allowEmpty('version');

        $validator
            ->scalar('description')
            ->allowEmpty('description');

        $validator
            ->scalar('script_dir')
            ->allowEmpty('script_dir');

        $validator
            ->scalar('plugin_dir')
            ->allowEmpty('plugin_dir');

        $validator
            ->scalar('build_command')
            ->allowEmpty('build_command');

        $validator
            ->scalar('user_vars')
            ->allowEmpty('user_vars');

        $validator
            ->scalar('test_command')
            ->allowEmpty('test_command');

        $validator
            ->scalar('config_files')
            ->allowEmpty('config_files');

        $validator
            ->scalar('depends')
            ->allowEmpty('depends');

        $validator
            ->scalar('external_depends')
            ->allowEmpty('external_depends');

        $validator
            ->scalar('suggests')
            ->allowEmpty('suggests');

        $validator
            ->scalar('package_ci')
            ->allowEmpty('package_ci');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): \Cake\ORM\RulesChecker
    {
        $rules->add($rules->existsIn(['package_id'], 'Packages'));

        return $rules;
    }
}
