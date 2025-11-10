<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * MOrder Model
 *
 * @method \App\Model\Entity\MOrder newEmptyEntity()
 * @method \App\Model\Entity\MOrder newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\MOrder> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\MOrder get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\MOrder findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\MOrder patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\MOrder> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\MOrder|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\MOrder saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\MOrder>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MOrder>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MOrder>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MOrder> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MOrder>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MOrder>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MOrder>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MOrder> deleteManyOrFail(iterable $entities, array $options = [])
 */
class MOrderTable extends Table
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

        $this->setTable('m_order');
        $this->setDisplayField('deli_order_id');
        $this->setPrimaryKey('deli_order_id');
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('user_id')
            ->maxLength('user_id', 15)
            ->allowEmptyString('user_id');

        $validator
            ->allowEmptyString('term_id');

        $validator
            ->scalar('order_status')
            ->maxLength('order_status', 1)
            ->allowEmptyString('order_status');

        $validator
            ->scalar('del_flg')
            ->maxLength('del_flg', 1)
            ->allowEmptyString('del_flg');

        $validator
            ->scalar('create_user')
            ->maxLength('create_user', 15)
            ->allowEmptyString('create_user');

        $validator
            ->dateTime('create_date')
            ->allowEmptyDateTime('create_date');

        $validator
            ->scalar('update_user')
            ->maxLength('update_user', 15)
            ->allowEmptyString('update_user');

        $validator
            ->dateTime('update_date')
            ->allowEmptyDateTime('update_date');

        return $validator;
    }
}
