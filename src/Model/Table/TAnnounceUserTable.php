<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Datasource\EntityInterface; // ★追加必須
use Cake\Event\EventInterface; // ← これを追加


/**
 * TAnnounceUser Model
 *
 * @method \App\Model\Entity\TAnnounceUser newEmptyEntity()
 * @method \App\Model\Entity\TAnnounceUser newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\TAnnounceUser> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\TAnnounceUser get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\TAnnounceUser findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\TAnnounceUser patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\TAnnounceUser> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\TAnnounceUser|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\TAnnounceUser saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\TAnnounceUser>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\TAnnounceUser>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\TAnnounceUser>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\TAnnounceUser> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\TAnnounceUser>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\TAnnounceUser>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\TAnnounceUser>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\TAnnounceUser> deleteManyOrFail(iterable $entities, array $options = [])
 */
class TAnnounceUserTable extends AppTable
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

        $this->setTable('t_announce_user');
        $this->setDisplayField('user_id');
        $this->setPrimaryKey(['announce_id', 'user_id']);

        $this->belongsTo('TAnnounce', [
            'foreignKey' => 'announce_id',
        ]);

        $this->belongsTo('MUser', [
            'foreignKey' => 'user_id',
            'bindingKey' => 'user_id',
        ]);


    }
    public function beforeSave(EventInterface $event, EntityInterface $entity, \ArrayObject $options): void

{
    if (!$entity->isNew() && $entity->get('create_date') !== null) {
        $entity->setDirty('create_date', false);
    }
}//作成日のため
    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator->remove('del_flg');

        $validator
            ->notEmptyString('user_id', 'このフィールドを入力してください。');

        $validator = parent::validationDefault($validator);
        $validator->remove('disp_no');
        return $validator;
    }
}
