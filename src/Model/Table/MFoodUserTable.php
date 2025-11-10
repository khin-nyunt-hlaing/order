<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * MFoodUser Model
 *
 * @method \App\Model\Entity\MFoodUser newEmptyEntity()
 * @method \App\Model\Entity\MFoodUser newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\MFoodUser> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\MFoodUser get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\MFoodUser findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\MFoodUser patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\MFoodUser> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\MFoodUser|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\MFoodUser saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\MFoodUser>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MFoodUser>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MFoodUser>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MFoodUser> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MFoodUser>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MFoodUser>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MFoodUser>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MFoodUser> deleteManyOrFail(iterable $entities, array $options = [])
 */
class MFoodUserTable extends AppTable
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
        
        $this->setTable('m_food_user');
        $this->setDisplayField('food_id');
        $this->setPrimaryKey(['food_id', 'user_id']);
        //追加
        $this->belongsTo('Mfoods', [
            'foreignKey' => 'food_id',
        ]);
        $this->belongsTo('MUser', [
            'foreignKey' => 'user_id',
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

        // food_id（必須、数値）
        $validator
            ->requirePresence('food_id')
            ->notEmptyString('food_id', 'このフィールドを入力してください。')
            ->add('food_id', 'halfwidthDigitsOnly', [
                'rule' => ['custom', '/^[A-Za-z0-9]+$/'],
                'message' => '半角数字のみで入力してください'
            ]);
        // user_id（必須、数値）
        $validator
            ->requirePresence('user_id')
            ->notEmptyString('user_id', 'このフィールドを入力してください。')
            ->add('user_id', 'alnum', [
                'rule' => ['custom', '/^[A-Za-z0-9]+$/'],
                'message' => '半角英数字のみで入力してください'
            ]);
        // food_idとuser_idの組み合わせはユニークであるべき
        $validator->add('user_id', 'uniqueCombo', [
            'rule' => function ($value, $context) {
                $foodId = $context['data']['food_id'] ?? null;
                if (!$foodId) {
                    return true; // food_idがないならチェック不要（他でエラーになる）
                }
                $exists = \Cake\ORM\TableRegistry::getTableLocator()
                    ->get('MFoodUser')
                    ->exists(['food_id' => $foodId, 'user_id' => $value]);
                return !$exists;
            },
            'message' => 'この施設はすでにこの食材に登録されています',
        ]);

        return $validator;
    }
}
