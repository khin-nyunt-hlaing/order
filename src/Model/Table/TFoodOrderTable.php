<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * TFoodOrder Model
 *
 * @method \App\Model\Entity\TFoodOrder newEmptyEntity()
 * @method \App\Model\Entity\TFoodOrder newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\TFoodOrder> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\TFoodOrder get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\TFoodOrder findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\TFoodOrder patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\TFoodOrder> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\TFoodOrder|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\TFoodOrder saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\TFoodOrder>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\TFoodOrder>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\TFoodOrder>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\TFoodOrder> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\TFoodOrder>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\TFoodOrder>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\TFoodOrder>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\TFoodOrder> deleteManyOrFail(iterable $entities, array $options = [])
 */
class TFoodOrderTable extends AppTable
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

        $this->setTable('t_food_order');
        $this->setDisplayField('food_order_id');
        $this->setPrimaryKey('food_order_id');
        
                $this->belongsTo('MFoods', [
            'foreignKey' => 'food_id', // TFoodOrder 側の外部キー
            'joinType' => 'INNER',     // 必要に応じて 'LEFT' に変更可
        ]);
        $this->belongsTo('MUsers', [
            'foreignKey' => 'user_id',
            'className' => 'MUser', // モデル名が単数なら className 必要
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('MFoodsOptional', [
            'className' => 'MFoods',
            'foreignKey' => 'food_id',
            'joinType' => 'LEFT',
        ]);

        $this->belongsTo('MFoodCategories', [
            'className' => 'MFoodCategories',
            'foreignKey' => 'category_id',
            'joinType' => 'LEFT'
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
            ->maxLength('user_id', 255)
            ->allowEmptyString('user_id');

        $validator
            ->date('order_date')
            ->allowEmptyDate('order_date');

        $validator
            ->date('deli_req_date')
            ->requirePresence('deli_req_date', 'create')
            ->notEmptyDate('deli_req_date', 'このフィールドを入力してください。');

        $validator
            ->requirePresence('category_id','create')
            ->notEmptyString('category_id', 'このフィールドを選択してください。');

        $validator
            ->requirePresence('food_id', 'create')
            ->notEmptyString('food_id', 'このフィールドを選択してください。'); 

        $validator
            ->integer('order_quantity')
            ->requirePresence('order_quantity', 'create')
            ->notEmptyString('order_quantity', 'このフィールドを入力してください。')
            ->add('order_quantity', 'maxValue', [
                'rule' => ['comparison', '<=', 999],
                'message' => '値は999以下にする必要があります。'
            ]);

        $validator
            ->scalar('order_status')
            ->maxLength('order_status', 1)
            ->allowEmptyString('order_status');

        return $validator;
    }
}
