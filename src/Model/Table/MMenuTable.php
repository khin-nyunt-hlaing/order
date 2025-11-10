<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * MMenu Model
 *
 * @method \App\Model\Entity\MMenu newEmptyEntity()
 * @method \App\Model\Entity\MMenu newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\MMenu> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\MMenu get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\MMenu findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\MMenu patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\MMenu> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\MMenu|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\MMenu saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\MMenu>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MMenu>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MMenu>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MMenu> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MMenu>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MMenu>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MMenu>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MMenu> deleteManyOrFail(iterable $entities, array $options = [])
 */
class MMenuTable extends AppTable
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

        $this->setTable('m_menu');
        $this->setDisplayField('menu_id');
        $this->setPrimaryKey('menu_id');
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
            ->scalar('menu_name')
            ->maxLength('menu_name', 60)
            ->allowEmptyString('menu_name');

        $validator
            ->scalar('controller')
            ->maxLength('controller', 100)
            ->allowEmptyString('controller');

        $validator
            ->scalar('action')
            ->maxLength('action', 100)
            ->allowEmptyString('action');

        $validator = parent::validationDefault($validator);
        return $validator;
    }
}
