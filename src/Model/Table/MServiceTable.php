<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * MService Model
 *
 * @method \App\Model\Entity\MService newEmptyEntity()
 * @method \App\Model\Entity\MService newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\MService> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\MService get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\MService findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\MService patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\MService> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\MService|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\MService saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\MService>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MService>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MService>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MService> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MService>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MService>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MService>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MService> deleteManyOrFail(iterable $entities, array $options = [])
 */
class MServiceTable extends AppTable
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

        $this->setTable('m_service');
        $this->setDisplayField('use_ervice_id');
        $this->setPrimaryKey('use_service_id');

            $this->hasMany('MUser', [
                'foreignKey' => 'use_service_id',
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

                // 1. food_id（主キー・手動入力・重複NG・数値）
        $validator
            ->notEmptyString('use_service_id', 'このフィールドを入力してください。')
            ->add('use_service_id', 'halfwidthDigitsOnly', [
                'rule' => ['custom', '/^[a-zA-Z0-9]+$/'],
                'message' => '半角数字のみで入力してください'
            ]);

        $validator
            ->scalar('service_name')
            ->maxLength('service_name', 100)
            ->allowEmptyString('service_name')
            ->add('service_name', 'widthLimit', [   // ★ルール名が必須
            'rule' => function ($value, $context) {
                return mb_strwidth((string)$value) <= 100; // 全角=2, 半角=1 計算
            },
            'message' => '入力可能桁数を超えています',
        ]);

        $validator = parent::validationDefault($validator);
        return $validator;
    }
}
