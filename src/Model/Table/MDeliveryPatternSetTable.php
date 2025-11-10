<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * MDeliveryPatternSet Model
 *
 * @method \App\Model\Entity\MDeliveryPatternSet newEmptyEntity()
 * @method \App\Model\Entity\MDeliveryPatternSet newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\MDeliveryPatternSet> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\MDeliveryPatternSet get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\MDeliveryPatternSet findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\MDeliveryPatternSet patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\MDeliveryPatternSet> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\MDeliveryPatternSet|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\MDeliveryPatternSet saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\MDeliveryPatternSet>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MDeliveryPatternSet>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MDeliveryPatternSet>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MDeliveryPatternSet> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MDeliveryPatternSet>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MDeliveryPatternSet>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MDeliveryPatternSet>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MDeliveryPatternSet> deleteManyOrFail(iterable $entities, array $options = [])
 */
class MDeliveryPatternSetTable extends AppTable
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
           
        $this->setTable('m_delivery_pattern_set');
        $this->setPrimaryKey(['use_pattern_id', 'delivery_id']);

       $this->belongsTo('MDelivery', [
            'foreignKey' => 'delivery_id',
            'joinType'   => 'INNER', 
        ]);

        $this->belongsTo('MDeliveryPattern', [
            'foreignKey' => 'use_pattern_id',
            'joinType'   => 'INNER',
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
            ->allowEmptyString('use_pattern_id');

        $validator
            ->allowEmptyString('delivery_id');


        $validator = parent::validationDefault($validator);
        return $validator;
    }
}
