<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * MUserGroup Model
 *
 * @method \App\Model\Entity\MUserGroup newEmptyEntity()
 * @method \App\Model\Entity\MUserGroup newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\MUserGroup> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\MUserGroup get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\MUserGroup findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\MUserGroup patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\MUserGroup> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\MUserGroup|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\MUserGroup saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\MUserGroup>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MUserGroup>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MUserGroup>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MUserGroup> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MUserGroup>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MUserGroup>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MUserGroup>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MUserGroup> deleteManyOrFail(iterable $entities, array $options = [])
 */
class MUserGroupTable extends AppTable
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

        $this->setTable('m_user_group');
        $this->setDisplayField('user_group_id');
        $this->setPrimaryKey('user_group_id');

        // 施設マスタと関連
        $this->hasMany('MUser', [
            'className' => 'App\Model\Table\MUserTable',
            'foreignKey' => 'user_group_id',
            'bindingKey' => 'user_group_id',
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
        $validator
        ->requirePresence('user_group_id', 'create')
        ->notEmptyString('user_group_id', 'このフィールドを選択してください');

        $validator
            ->scalar('user_group_name')
            ->maxLength('user_group_name', 100)
            ->notEmptyString('user_group_name', 'このフィールドを入力してください。')
            ->add('user_group_name', 'maxLengthByWidth', [
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
            
        $validator = parent::validationDefault($validator);
        return $validator;
    }

   public function buildRules(RulesChecker $rules): RulesChecker
{
    $rules->addDelete(function ($entity, $options) {
        $MUserTable = TableRegistry::getTableLocator()->get('MUser');

        $prefix = substr((string)$entity->user_group_id, 0, 5);

        $count = $MUserTable->find()
            ->where(function (QueryExpression $exp, $q) use ($prefix) {
                // use_service_id LIKE 'prefix%'
                return $exp->and_([
                    $q->newExpr()->like('use_service_id', $prefix . '%'),
                    $exp->eq('del_flg', 0)
                ]);
            })
            ->count();

        return $count === 0;
    }, 'hasMuser', [
        'errorField' => 'user_group_id',
        'message' => 'この施設グループは施設マスタで使用されているため、削除できません。'
    ]);

    return $rules;
}

} 
