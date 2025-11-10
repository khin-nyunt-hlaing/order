<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * MDispUser Model
 *
 * @method \App\Model\Entity\MDispUser newEmptyEntity()
 * @method \App\Model\Entity\MDispUser newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\MDispUser> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\MDispUser get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\MDispUser findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\MDispUser patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\MDispUser> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\MDispUser|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\MDispUser saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\MDispUser>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MDispUser>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MDispUser>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MDispUser> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MDispUser>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MDispUser>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MDispUser>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MDispUser> deleteManyOrFail(iterable $entities, array $options = [])
 */
class MDispUserTable extends AppTable
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('m_disp_user');
        $this->setDisplayField('user_id');
        $this->setPrimaryKey(['user_id', 'disp_user_id']);

        $this->belongsTo('MUser', [
            'className' => 'MUser',
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
            
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator = parent::validationDefault($validator);

        $validator->remove('disp_no');
        $validator->remove('del_flg');

        $validator
            ->requirePresence('user_id')
            ->maxLength('user_id', 8)
            ->notEmptyString('user_id', 'このフィールドを入力してください。');

        $validator
            ->requirePresence('disp_user_id')
            ->maxLength('disp_user_id', 8)
            ->notEmptyString('disp_user_id', 'このフィールドを入力してください。');

        return $validator;
    }
}
