<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * TDeliOrder Model
 *
 * @method \App\Model\Entity\TDeliOrder newEmptyEntity()
 * @method \App\Model\Entity\TDeliOrder newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\TDeliOrder> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\TDeliOrder get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\TDeliOrder findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\TDeliOrder patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\TDeliOrder> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\TDeliOrder|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\TDeliOrder saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\TDeliOrder>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\TDeliOrder>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\TDeliOrder>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\TDeliOrder> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\TDeliOrder>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\TDeliOrder>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\TDeliOrder>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\TDeliOrder> deleteManyOrFail(iterable $entities, array $options = [])
 */
class TDeliOrderTable extends AppTable
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

        $this->setTable('t_deli_order');
        $this->setDisplayField('deli_order_id');
        $this->setPrimaryKey('deli_order_id');

        $this->belongsTo('MTerm', [
            'foreignKey' => 'term_id',
            'joinType' => 'LEFT', // 必要に応じて INNER
        ]);

        $this->belongsTo('MUser', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER', // 必要に応じて LEFT に
        ]);

        $this->hasMany('TDeliOrderDtl', [
            'foreignKey' => 'deli_order_id',
            'className' => 'TDeliOrderDtl',
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

        $validator
        ->allowEmptyString('deli_order_id'); // IDENTITY対応：create時はnull許可

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

        return $validator;
    }
}
