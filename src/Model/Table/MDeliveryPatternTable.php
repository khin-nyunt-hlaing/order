<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * MDeliveryPattern Model
 *
 * @method \App\Model\Entity\MDeliveryPattern newEmptyEntity()
 * @method \App\Model\Entity\MDeliveryPattern newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\MDeliveryPattern> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\MDeliveryPattern get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\MDeliveryPattern findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\MDeliveryPattern patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\MDeliveryPattern> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\MDeliveryPattern|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\MDeliveryPattern saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\MDeliveryPattern>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MDeliveryPattern>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MDeliveryPattern>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MDeliveryPattern> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MDeliveryPattern>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MDeliveryPattern>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MDeliveryPattern>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MDeliveryPattern> deleteManyOrFail(iterable $entities, array $options = [])
 */
class MDeliveryPatternTable extends AppTable
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

        $this->setTable('m_delivery_pattern');
        $this->setDisplayField('use_pattern_id');
        $this->setPrimaryKey('use_pattern_id');

        $this->hasMany('MDeliveryPatternSet', [
        'foreignKey' => 'use_pattern_id',
        'dependent' => true,           // 親削除時に子も削除したいなら
        'saveStrategy' => 'replace',   // 編集時に子を差し替えるなら
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

        $validator
            ->allowEmptyString('use_pattern_id');

        $validator
            ->notEmptyString('delivery_pattern_name', 'このフィールドを入力してください。')
            ->scalar('delivery_pattern_name')
            ->maxLength('delivery_pattern_name', 200)
            ->allowEmptyString('delivery_pattern_name')
            ->add('delivery_pattern_name', 'maxLengthByWidth', [
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
                   return $length <= 200;
                },
                'message' => '入力可能桁数を超えています。'
            ]);

        return $validator;
    }
}
