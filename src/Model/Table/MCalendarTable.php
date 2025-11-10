<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * MCalendar Model
 *
 * @method \App\Model\Entity\MCalendar newEmptyEntity()
 * @method \App\Model\Entity\MCalendar newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\MCalendar> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\MCalendar get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\MCalendar findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\MCalendar patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\MCalendar> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\MCalendar|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\MCalendar saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\MCalendar>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MCalendar>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MCalendar>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MCalendar> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MCalendar>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MCalendar>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MCalendar>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MCalendar> deleteManyOrFail(iterable $entities, array $options = [])
 */
class MCalendarTable extends Table
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

        $this->setTable('m_Calendar');
        $this->setDisplayField('calendar_date');
        $this->setPrimaryKey('calendar_date');
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

        // $validator
        //     ->scalar('holiday_flg')
        //     ->maxLength('holiday_flg', 1)
        //     ->inList('holiday_flg', ['0', '1'], '祝日フラグは0か1で入力してください')
        //     ->allowEmptyString('holiday_flg');

        // return $validator;

        $validator
            ->scalar('holiday_flg')
            ->maxLength('holiday_flg', 1)
            ->allowEmptyString('holiday_flg')
            ->add('holiday_flg', 'strictInList', [
                'rule' => function ($value, $context) {
                    return in_array($value, [0, 1], true);
                },
                'message' => '祝日フラグは0か1で入力してください',
            ]);

        return $validator;
    }
}
