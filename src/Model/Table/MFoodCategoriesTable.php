<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * MFoodCategories Model
 *
 * @method \App\Model\Entity\MFoodCategory newEmptyEntity()
 * @method \App\Model\Entity\MFoodCategory newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\MFoodCategory> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\MFoodCategory get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\MFoodCategory findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\MFoodCategory patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\MFoodCategory> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\MFoodCategory|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\MFoodCategory saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\MFoodCategory>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MFoodCategory>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MFoodCategory>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MFoodCategory> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MFoodCategory>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MFoodCategory>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MFoodCategory>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MFoodCategory> deleteManyOrFail(iterable $entities, array $options = [])
 */
class MFoodCategoriesTable extends AppTable
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

        $this->setTable('M_FOOD_CATEGORY');
        $this->setDisplayField('category_id');
        $this->setPrimaryKey('category_id');
        // $this->getSchema()->setColumnType('category_id', 'biginteger');
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
            ->scalar('category_name')
            ->maxLength('category_name', 100)
            ->requirePresence('category_name', 'create')
            ->notEmptyString('category_name', 'このフィールドを入力してください。')
             ->add('category_name', 'maxByteLength', [
                'rule' => function ($value, $context) {
                   if (!is_string($value)) {
                    return false;
                   }
                   return strlen(mb_convert_encoding($value, 'UTF-8')) <= 100;
                },
                'message' => '入力可能桁数を超えています。'
            ]);
            
        $validator = parent::validationDefault($validator);
        return $validator;
    }
}
