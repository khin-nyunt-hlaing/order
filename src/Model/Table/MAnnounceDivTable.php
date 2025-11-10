<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * MAnnounceDiv Model
 *
 * @method \App\Model\Entity\MAnnounceDiv newEmptyEntity()
 * @method \App\Model\Entity\MAnnounceDiv newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\MAnnounceDiv> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\MAnnounceDiv get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\MAnnounceDiv findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\MAnnounceDiv patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\MAnnounceDiv> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\MAnnounceDiv|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\MAnnounceDiv saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\MAnnounceDiv>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MAnnounceDiv>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MAnnounceDiv>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MAnnounceDiv> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MAnnounceDiv>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MAnnounceDiv>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MAnnounceDiv>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MAnnounceDiv> deleteManyOrFail(iterable $entities, array $options = [])
 */
class MAnnounceDivTable extends AppTable
{
        public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('m_announce_div');
        $this->setDisplayField('announce_div');
        $this->setPrimaryKey('announce_div');
    }
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('announce_div_name')
            ->maxLength('announce_div_name', 20)
            ->notEmptyString('announce_div_name', 'このフィールドを入力してください。')
            ->add('announce_div_name', 'maxLengthByWidth', [
                'rule' => function ($value, $context) {
                    if (!is_string($value)) {
                        return false;
                    }
        
                    $length = 0;
                    // 文字ごとに判定
                    for ($i = 0; $i < mb_strlen($value, 'UTF-8'); $i++) {
                        $char = mb_substr($value, $i, 1, 'UTF-8');
                        // 全角かどうか判定（全角なら2、半角なら1を加算）
                        if (preg_match('/[^\x01-\x7E\xA1-\xDF]/u', $char)) {
                            $length += 2;
                        } else {
                            $length += 1;
                        }
                    }
                    // 全角10文字＝20 半角20文字＝20なので合計20文字分まで許容
                    return $length <= 20;
                },
                'message' => '入力可能桁数を超えています。'
            ]);

        $validator = parent::validationDefault($validator);
        return $validator;
    }
}
