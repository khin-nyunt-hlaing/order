<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * MAuth Model
 *
 * @method \App\Model\Entity\MAuth newEmptyEntity()
 * @method \App\Model\Entity\MAuth newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\MAuth> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\MAuth get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\MAuth findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\MAuth patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\MAuth> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\MAuth|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\MAuth saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\MAuth>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MAuth>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MAuth>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MAuth> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MAuth>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MAuth>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MAuth>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MAuth> deleteManyOrFail(iterable $entities, array $options = [])
 */
class MAuthTable extends Table
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

        $this->setTable('m_auth');
        $this->setDisplayField('user_id');
        $this->setPrimaryKey(['user_id', 'view_user_id', 'menu_id']);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator->remove('disp_no');
        $validator->remove('del_flg');

        $validator
            ->scalar('use_div')
            ->maxLength('use_div', 1)
            ->allowEmptyString('use_div');

        $validator = parent::validationDefault($validator);
        return $validator;
    }
}
