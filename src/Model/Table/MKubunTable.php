<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * MKubun Model
 *
 * @method \App\Model\Entity\MKubun newEmptyEntity()
 * @method \App\Model\Entity\MKubun newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\MKubun> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\MKubun get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\MKubun findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\MKubun patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\MKubun> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\MKubun|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\MKubun saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\MKubun>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MKubun>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MKubun>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MKubun> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MKubun>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MKubun>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MKubun>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MKubun> deleteManyOrFail(iterable $entities, array $options = [])
 */
class MKubunTable extends AppTable
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

        $this->setTable('m_kubun');
        $this->setDisplayField('kubun_cd');
        $this->setPrimaryKey(['kubun_cd', 'kubun_value']);
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
            ->scalar('kubun_name')
            ->maxLength('kubun_name', 100)
            ->allowEmptyString('kubun_name');

        // 5. disp_no（数字限定・name属性あり）
        $validator
            ->notEmptyString('disp_no', 'このフィールドを入力してください。')
            ->integer('disp_no', '表示順は半角数値で入力してください。');

        $validator
            ->scalar('del_flg')               // 型：文字列
            ->maxLength('del_flg', 1)         // 1文字制限
            ->allowEmptyString('del_flg')     // 空白OKを追加
            ->inList('del_flg', ['0', '1'], '0または1を入力してください');

        $validator = parent::validationDefault($validator);
        return $validator;
    }
}
