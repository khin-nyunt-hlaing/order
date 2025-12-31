<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * MUser Model
 *
 * @method \App\Model\Entity\MUser newEmptyEntity()
 * @method \App\Model\Entity\MUser newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\MUser> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\MUser get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\MUser findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\MUser patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\MUser> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\MUser|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\MUser saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\MUser>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MUser>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MUser>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MUser> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MUser>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MUser>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MUser>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MUser> deleteManyOrFail(iterable $entities, array $options = [])
 */
class MUserTable extends AppTable
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

        $this->setTable('m_user');
        $this->setDisplayField('user_id');
        $this->setPrimaryKey('user_id');
        
        $this->belongsTo('MService', [
            'foreignKey' => 'use_service_id',
            'propertyName' => 'service',
            ]);

        $this->belongsTo('MDeliveryPattern', [
            'foreignKey' => 'use_pattern_id',
            'propertyName' => 'delivery_pattern', // 任意：省略可能
            'className' => 'MDeliveryPattern',
            'bindingKey' => 'use_pattern_id',
            'joinType' => 'LEFT'
        ]);

        $this->belongsToMany('Mfoods', [
            'joinTable' => 'm_food_user',
            'foreignKey' => 'user_id',
            'targetForeignKey' => 'food_id',
        ]);

        $this->hasMany('MFoodUser', [
            'foreignKey' => 'user_id',
        ]);

        //MUserとMUserが中間テーブルm_disp_userを介して多対多で関連付く、用
        $this->belongsToMany('ViewedUsers', [
            'className' => 'MUser',
            'joinTable' => 'm_disp_user',
            'foreignKey' => 'user_id',
            'targetForeignKey' => 'disp_user_id'
        ]);

        $this->hasMany('MDispUser', [
            'className' => 'MDispUser',
            'foreignKey' => 'user_id'
        ]);

        $this->belongsTo('MUserGroups', [
            'foreignKey' => 'user_group_id',
            'bindingKey' => 'user_group_id',
            'className'  => 'MUserGroup',
        ]);


    }
    
        

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     * @var int
     */
    public int $minLeadTime = 0;
    public function validationDefault(Validator $validator): Validator
    {
        //施設番号
        $validator
            ->maxLength('user_id', 15)
            ->requirePresence('user_id', 'create')
            ->notEmptyString('user_id', 'このフィールドを入力してください。')
            ->add('user_id', 'alphanumeric', [
                'rule' => ['custom', '/^[a-zA-Z0-9]+$/'],
                'message' => '施設番号は半角英数字で入力してください。'
            ]);
;

        //施設名称    
        $validator
        ->scalar('user_name')
        ->requirePresence('user_name', 'create')
        ->maxLength('user_name', 100)
        ->notEmptyString('user_name', 'このフィールドを入力してください。')
        ->add('user_name', 'maxLengthByWidth', [
                'rule' => function ($value, $context) {
                   if (!is_string($value)) {
                    return false;
                   }
                  
                   $length = 0;
                   
                   for ($i = 0; $i < mb_strlen($value, 'UTF-8'); $i++) {
                    $char = mb_substr($value, $i, 1, 'UTF-8');

                    if (preg_match('/[^\x01-\x7E\xA1-\xDF]/u', $char)) {
                        $length += 2;
                    } else {
                        $length += 1;
                    }
                   }
                   return $length <= 100;
                },
                'message' => '入力可能桁数を超えています。'
            ]);

        //パスワード    
        $validator
            ->scalar('password')
            ->maxLength('password', 100)
            ->requirePresence('password', 'create')
            ->notEmptyString('password', 'このフィールドを入力してください。', 'create')
             ->add('user_name', 'maxLengthByWidth', [
                'rule' => function ($value, $context) {
                   if (!is_string($value)) {
                    return false;
                   }
                  
                   $length = 0;
                   
                   for ($i = 0; $i < mb_strlen($value, 'UTF-8'); $i++) {
                    $char = mb_substr($value, $i, 1, 'UTF-8');

                    if (preg_match('/[^\x01-\x7E\xA1-\xDF]/u', $char)) {
                        $length += 2;
                    } else {
                        $length += 1;
                    }
                   }
                   return $length <= 100;
                },
                'message' => '入力可能桁数を超えています。'
            ]);

        //秘密の質問    
        $validator
            ->scalar('question')
            ->maxLength('question', 1)
        //    ->requirePresence('question', 'create')
            ->allowEmptyString('question');

        //質問の答え    
        $validator
            ->scalar('answer')
            ->maxLength('answer', 60)
            ->allowEmptyString('answer');

        //利用状態
        $validator
            ->scalar('status')
            ->maxLength('status', 1)
            ->notEmptyString('status', 'このフィールドを選択してください');

        //発注サービス    
        $validator
            ->scalar('use_service_id')                      // スカラー値（文字列 or 数値）を要求
            ->maxLength('use_service_id', 1)                // 最大1桁（IDが1桁ならOK）
            ->requirePresence('use_service_id', 'create')   // create時は必須
            ->notEmptyString('use_service_id', 'このフィールドを選択してください');

        //配食商品パターン
        $validator
            ->scalar('use_pattern_id')
            ->maxLength('use_pattern_id', 2);

        //リードタイム   
        $minLeadTime = $this->minLeadTime ?? 0; 
        $validator
            ->integer('read_time', 'リードタイムは半角数値で入力してください。')
            ->greaterThanOrEqual(
                'read_time', $minLeadTime, "リードタイムは{$minLeadTime}日以上で入力してください");

        // $validator
        //     ->requirePresence('user_group_id', 'create')               // ← セレクトの name に合わせる
        //     ->notEmptyString('user_group_id', 'このフィールドを選択してください');

        $validator = parent::validationDefault($validator);
        return $validator;
    }

    // in src/Model/Table/MUserTable.php
public function buildRules(RulesChecker $rules): RulesChecker
{
    $rules->add(function ($entity, $options) {
        $max = pow(10, 18) - 1;

        foreach ($entity->toArray() as $field => $value) {
            if (is_numeric($value) && $value > $max) {
                // どの項目でも超えてたらまとめてエラー
                $entity->setError($field, ['入力可能桁数を超えています。']);
                return false;
            }
        }
        return true;
    }, 'checkNumericLimit');

    return $rules;
}

}
