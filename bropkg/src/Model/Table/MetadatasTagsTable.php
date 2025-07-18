<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * MetadatasTags Model
 *
 * @property \App\Model\Table\MetadatasTable|\Cake\ORM\Association\BelongsTo $Metadatas
 * @property \App\Model\Table\TagsTable|\Cake\ORM\Association\BelongsTo $Tags
 *
 * @method \App\Model\Entity\MetadatasTag get($primaryKey, $options = [])
 * @method \App\Model\Entity\MetadatasTag newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\MetadatasTag[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\MetadatasTag|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\MetadatasTag patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\MetadatasTag[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\MetadatasTag findOrCreate($search, callable $callback = null, $options = [])
 */
class MetadatasTagsTable extends Table
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

        $this->setTable('metadatas_tags');
        $this->setDisplayField('metadata_id');
        $this->setPrimaryKey(['metadata_id', 'tag_id']);

        $this->belongsTo('Metadatas', [
            'foreignKey' => 'metadata_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Tags', [
            'foreignKey' => 'tag_id',
            'joinType' => 'INNER'
        ]);
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
        $rules->add($rules->existsIn(['metadata_id'], 'Metadatas'));
        $rules->add($rules->existsIn(['tag_id'], 'Tags'));

        return $rules;
    }
}
