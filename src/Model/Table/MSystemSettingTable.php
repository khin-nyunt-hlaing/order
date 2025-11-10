<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * MSystemSetting Model
 *
 * @method \App\Model\Entity\MSystemSetting newEmptyEntity()
 * @method \App\Model\Entity\MSystemSetting newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\MSystemSetting> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\MSystemSetting get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\MSystemSetting findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\MSystemSetting patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\MSystemSetting> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\MSystemSetting|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\MSystemSetting saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\MSystemSetting>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MSystemSetting>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MSystemSetting>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MSystemSetting> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MSystemSetting>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MSystemSetting>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MSystemSetting>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MSystemSetting> deleteManyOrFail(iterable $entities, array $options = [])
 */
class MSystemSettingTable extends AppTable
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

        $this->setTable('m_system_setting');
        $this->setDisplayField('system_id');
        $this->setPrimaryKey('system_id');
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
            ->decimal('deli_min_chk')
            ->allowEmptyString('deli_min_chk')
            ->add('deli_min_chk', 'numericAndMaxDigits', [
                'rule' => function ($value, $context) {
                    return (bool)preg_match('/^\d{1,18}$/', (string)$value);
                },
                'message' => '入力可能桁数を超えています。'
            ]);

        $validator
            ->decimal('deli_chg_chk')
            ->allowEmptyString('deli_chg_chk')
            ->add('deli_chg_chk', 'numericAndMaxDigits', [
                'rule' => function ($value, $context) {
                    return (bool)preg_match('/^\d{1,18}$/', (string)$value);
                },
                'message' => '入力可能桁数を超えています。'
            ]);

        $validator
            ->decimal('deli_req_chk')
            ->allowEmptyString('deli_req_chk')
            ->add('deli_req_chk', 'numericAndMaxDigits', [
                'rule' => function ($value, $context) {
                    return (bool)preg_match('/^\d{1,18}$/', (string)$value);
                },
                'message' => '入力可能桁数を超えています。'
            ]);

        $validator->remove('disp_no');
        $validator->remove('del_flg');
        $validator->remove('create_user');
        return $validator;
    }
}
