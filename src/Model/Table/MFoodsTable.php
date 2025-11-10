<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\I18n\FrozenTime;

/**
 * Mfoods Model
 *
 * @method \App\Model\Entity\Mfood newEmptyEntity()
 * @method \App\Model\Entity\Mfood newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Mfood> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Mfood get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Mfood findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Mfood patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Mfood> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Mfood|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Mfood saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Mfood>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Mfood>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Mfood>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Mfood> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Mfood>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Mfood>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Mfood>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Mfood> deleteManyOrFail(iterable $entities, array $options = [])
 */
class MfoodsTable extends AppTable
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
        
        $this->setTable('m_food');
        $this->setDisplayField('food_id');
        $this->setPrimaryKey('food_id');

    //追加: m_food_categoryテーブルとのリレーション設定
         $this->belongsTo('MFoodCategories', [
        'foreignKey' => 'category_id',
        'joinType' => 'LEFT',
    ]);
        //m_userテーブルとのリレーション
        $this->belongsToMany('MUsers', [
        'className' => 'MUser',//実クラス名が単数形でもOKにする    
        'joinTable' => 'm_food_user',
        'foreignKey' => 'food_id',
        'targetForeignKey' => 'user_id',
    ]);
        //m_food_userとのリレーション
        $this->hasMany('MFoodUser', [
        'foreignKey' => 'food_id',
        'className' => 'MFoodUser',
    ]);
    //発注テーブルとのリレーション（削除チェック用）
    $this->hasMany('TFoodOrders', [
        'className' => 'TFoodOrder',
        'foreignKey' => 'food_id',
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

        // 1. food_id（主キー・手動入力・重複NG・数値）
        $validator
            ->requirePresence('food_id', 'create')   //新規作成時に適用
            ->maxLength('food_id', 6)
            ->notEmptyString('food_id', 'このフィールドを入力してください。')   // 空文字防止
            ->add('food_id', 'halfwidthDigitsOnly', [
                'rule' => ['custom', '/^[a-zA-Z0-9]+$/'],
                'message' => 'コード番号は半角英数で入力してください。'
            ])
            ->add('food_id', 'unique', [
                'rule' => 'validateUnique',
                'provider' => 'table',
                'message' => '登録済みのコード番号が入力されています。'
            ]);

        // 2. food_name（全文字種許可、特に制限なし）
        $validator
            ->scalar('food_name')
            ->requirePresence('food_name')
            ->notEmptyString('food_name', 'このフィールドを入力してください。')
            ->add('food_name', 'maxLengthByWidth', [
                'rule' => function ($value, $context) {
                    if (!is_string($value)) {
                        return false;
                    }
        
                    $length = 0;
                    // 文字ごとに判定
                    for ($i = 0; $i < mb_strlen($value, 'UTF-8'); $i++) {
                        $char = mb_substr($value, $i, 1, 'UTF-8');
                        // 全角かどうか判定（全角なら2、半角なら1を加算）
                        if (preg_match('/[^\x01-\x7E\xA1-\xDF]/u', $char)) {
                            $length += 2;
                        } else {
                            $length += 1;
                        }
                    }
                    // 全角10文字＝20 半角20文字＝20なので合計20文字分まで許容
                    return $length <= 200;
                },
                'message' => '入力可能桁数を超えています。'
            ]);

        // 3. category_id（セレクトボックス・存在チェック）
        $validator
            ->scalar('category_id')
            ->maxLength('category_id', 6)
            ->requirePresence('category_id')
            ->notEmptyString('category_id', 'このフィールドを選択してください。');

        // 4. food_specification（任意項目・文字列、内容の種類制限は必要に応じて）
        $validator
            ->scalar('food_specification')
            ->allowEmptyString('food_specification')
            ->add('food_specification', 'maxLengthByWidth', [
                'rule' => function ($value, $context) {
                    if (!is_string($value)) {
                        return false;
                    }
        
                    $length = 0;
                    // 文字ごとに判定
                    for ($i = 0; $i < mb_strlen($value, 'UTF-8'); $i++) {
                        $char = mb_substr($value, $i, 1, 'UTF-8');
                        // 全角かどうか判定（全角なら2、半角なら1を加算）
                        if (preg_match('/[^\x01-\x7E\xA1-\xDF]/u', $char)) {
                            $length += 2;
                        } else {
                            $length += 1;
                        }
                    }
                    // 全角10文字＝20 半角20文字＝20なので合計20文字分まで許容
                    return $length <= 20;
                },
                'message' => '入力可能桁数を超えています。'
            ]);

        // $validator
        // ->requirePresence('user_group_id', 'create')            // ← セレクトの name に合わせる
        //->notEmptyString('user_group_id', 'このフィールドを選択してください');

        
        return $validator;
    }

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
