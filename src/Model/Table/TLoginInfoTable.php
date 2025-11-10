<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * TLoginInfo Model
 *
 * @method \App\Model\Entity\TLoginInfo newEmptyEntity()
 * @method \App\Model\Entity\TLoginInfo newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\TLoginInfo> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\TLoginInfo get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\TLoginInfo findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\TLoginInfo patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\TLoginInfo> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\TLoginInfo|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\TLoginInfo saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\TLoginInfo>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\TLoginInfo>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\TLoginInfo>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\TLoginInfo> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\TLoginInfo>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\TLoginInfo>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\TLoginInfo>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\TLoginInfo> deleteManyOrFail(iterable $entities, array $options = [])
 */
class TLoginInfoTable extends AppTable
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

        $this->setTable('t_login_info');
        $this->setDisplayField('user_id');
        $this->setPrimaryKey(['user_id', 'login_date']);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator->remove('disp_no');
        $validator->remove('del_flg');

        $validator
            ->scalar('login_result')
            ->maxLength('login_result', 1)
            ->allowEmptyString('login_result');

        $validator = parent::validationDefault($validator);
        return $validator;
    }
}
