<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * MDelivery Model
 *
 * @method \App\Model\Entity\MDelivery newEmptyEntity()
 * @method \App\Model\Entity\MDelivery newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\MDelivery> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\MDelivery get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\MDelivery findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\MDelivery patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\MDelivery> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\MDelivery|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\MDelivery saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\MDelivery>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MDelivery>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MDelivery>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MDelivery> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MDelivery>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MDelivery>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MDelivery>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MDelivery> deleteManyOrFail(iterable $entities, array $options = [])
 */
class MDeliveryTable extends AppTable
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


        $this->setTable('m_delivery');
        $this->setDisplayField('delivery_id');
        $this->setPrimaryKey('delivery_id');
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
            ->allowEmptyString('delivery_id');

        $validator
            ->requirePresence('delivery_name', 'create') // 新規登録時に必須
            ->notEmptyString('delivery_name',  'このフィールドを入力してください。')
            ->scalar('delivery_name')
            ->maxLength('delivery_name', 200, '200文字以内で入力してください')
            ->add('delivery_name', 'maxByteLength', [
                'rule' => function ($value, $context) {
                   if (!is_string($value)) {
                    return false;
                   }
                   return strlen(mb_convert_encoding($value, 'UTF-8')) <= 200;
                },
                'message' => '入力可能桁数を超えています。'
            ]);

        

        $validator = parent::validationDefault($validator);
        return $validator;
    }
}
