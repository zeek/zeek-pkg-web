<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Metadata Entity
 *
 * @property string $id
 * @property string $package_id
 * @property string $version
 * @property string $description
 * @property string $script_dir
 * @property string $plugin_dir
 * @property string $build_command
 * @property string $user_vars
 * @property string $test_command
 * @property string $config_files
 * @property string $depends
 * @property string $external_depends
 * @property string $suggests
 * @property string $package_ci
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Package $package
 * @property \App\Model\Entity\Tag[] $tags
 */
class Metadata extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false
    ];
}
