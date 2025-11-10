<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * TDeliOrderDtl Model
 *
 * @method \App\Model\Entity\TDeliOrderDtl newEmptyEntity()
 * @method \App\Model\Entity\TDeliOrderDtl newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\TDeliOrderDtl> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\TDeliOrderDtl get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\TDeliOrderDtl findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\TDeliOrderDtl patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\TDeliOrderDtl> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\TDeliOrderDtl|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\TDeliOrderDtl saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\TDeliOrderDtl>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\TDeliOrderDtl>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\TDeliOrderDtl>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\TDeliOrderDtl> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\TDeliOrderDtl>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\TDeliOrderDtl>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\TDeliOrderDtl>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\TDeliOrderDtl> deleteManyOrFail(iterable $entities, array $options = [])
 */
class TDeliOrderDtlTable extends AppTable
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

        $this->setTable('t_deli_order_dtl');
        $this->setDisplayField(['deli_order_dtl_id']);
        $this->setPrimaryKey('deli_order_dtl_id');


        $this->addBehavior('Timestamp', [
        'events' => [
        'Model.beforeSave' => [
            'create_date' => 'new',
            'update_date' => 'always'
      ]
    ]
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
            ->date('term_date')
            ->allowEmptyDate('term_date', 'このフィールドを入力してください。');

        $validator
            ->allowEmptyString('delivery_id', 'このフィールドを入力してください。');

        $validator
            ->requirePresence('quantity')
            ->notEmptyString('quantity', '数値を入力してください')
            ->integer('quantity')
            ->allowEmptyString('quantity')
            ->lessThanOrEqual('quantity', 999);

        return $validator;
    }

}
