<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * TFoodOrderFix Model
 *
 * @method \App\Model\Entity\TFoodOrderFix newEmptyEntity()
 * @method \App\Model\Entity\TFoodOrderFix newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\TFoodOrderFix> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\TFoodOrderFix get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\TFoodOrderFix findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\TFoodOrderFix patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\TFoodOrderFix> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\TFoodOrderFix|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\TFoodOrderFix saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\TFoodOrderFix>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\TFoodOrderFix>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\TFoodOrderFix>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\TFoodOrderFix> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\TFoodOrderFix>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\TFoodOrderFix>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\TFoodOrderFix>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\TFoodOrderFix> deleteManyOrFail(iterable $entities, array $options = [])
 */
class TFoodOrderFixTable extends AppTable
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

        $this->setTable('t_food_order_fix');
        $this->setDisplayField('food_order_id');
        $this->setPrimaryKey('food_order_id');

        $this->belongsTo('TFoodOrder', [
            'foreignKey' => 'food_order_id', // TFoodOrder 側の外部キー
            'joinType' => 'INNER',     // 必要に応じて 'LEFT' に変更可
        ]);
        $this->belongsTo('MFoods', [
            'className'  => 'MFoods',
            'foreignKey' => 'food_id',
            'bindingKey' => 'food_id',
            'joinType'   => 'INNER',
        ]);
        $this->belongsTo('MUsers', [
            'foreignKey' => 'user_id',
            'className' => 'MUser', // モデル名が単数なら className 必要
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

        $validator
            ->scalar('user_id')
            ->maxLength('user_id', 15)
            ->allowEmptyString('user_id');

        $validator
            ->date('order_date')
            ->allowEmptyDate('order_date', 'このフィールドを入力してください。');

        $validator
            ->date('deli_req_date')
            ->allowEmptyDate('deli_req_date', 'このフィールドを入力してください。');

        $validator
            ->date('deli_shedule_date')
            ->allowEmptyDate('deli_shedule_date', 'このフィールドを入力してください。');
        
        $validator
            ->date('deli_confirm_date')->allowEmptyDate('deli_confirm_date');
        
        $validator
            ->date('export_confirm_date')->allowEmptyDate('export_confirm_date');

        $validator
            ->scalar('food_id')
            ->maxLength('food_id', 6)
            ->allowEmptyString('food_id');

        $validator
            ->integer('order_quantity')
            ->allowEmptyString('order_quantity')
            ->lessThanOrEqual('order_quantity', 999);

        $validator
            ->scalar('order_status')
            ->maxLength('order_status', 1)
            ->allowEmptyString('order_status');

        return $validator;
    }
    
}
