<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * TAnnounce Model
 *
 * @method \App\Model\Entity\TAnnounce newEmptyEntity()
 * @method \App\Model\Entity\TAnnounce newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\TAnnounce> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\TAnnounce get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\TAnnounce findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\TAnnounce patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\TAnnounce> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\TAnnounce|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\TAnnounce saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\TAnnounce>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\TAnnounce>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\TAnnounce>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\TAnnounce> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\TAnnounce>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\TAnnounce>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\TAnnounce>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\TAnnounce> deleteManyOrFail(iterable $entities, array $options = [])
 */
class TAnnounceTable extends AppTable
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

        $this->setTable('t_announce');
        $this->setDisplayField('ANNOUNCE_TEXT');
        $this->setPrimaryKey('announce_id');
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
            ->scalar('announce_div')
            ->maxLength('announce_div', 5)
            ->notEmptyString('announce_div', 'このフィールドを選択してください。');

        $validator
            ->date('announce_start_date')
            ->requirePresence('announce_start_date', 'create')
            ->notEmptyDate('announce_start_date', 'このフィールドを入力してください。');

        $validator
            ->date('announce_end_date')
            ->requirePresence('announce_end_date', 'create')
            ->notEmptyDate('announce_end_date', 'このフィールドを入力してください。');

        $validator
            ->scalar('announce_title')
            ->maxLength('announce_title', 30)
            ->notEmptyString('announce_title', 'このフィールドを入力してください。');

        $validator
            ->scalar('announce_text')
            ->maxLength('announce_text', 2000)
            ->allowEmptyString('announce_text');

        $validator
            ->scalar('temp_filename1')
            ->maxLength('temp_filename1', 30)
            ->allowEmptyString('temp_filename1');

        $validator
            ->scalar('temp_filename2')
            ->maxLength('temp_filename2', 30)
            ->allowEmptyString('temp_filename2');

        $validator
            ->scalar('temp_filename3')
            ->maxLength('temp_filename3', 30)
            ->allowEmptyString('temp_filename3');

        $validator
            ->scalar('temp_filename4')
            ->maxLength('temp_filename4', 30)
            ->allowEmptyString('temp_filename4');

        $validator
            ->scalar('temp_filename5')
            ->maxLength('temp_filename5', 30)
            ->allowEmptyString('temp_filename5');

        $validator
            ->scalar('visibility')
            ->maxLength('visibility', 1)
            ->allowEmptyString('visibility');

        $validator
            ->allowEmptyFile('attachment') // 空のファイルはOKにする
            ->add('attachment', 'pdfOnly', [
                'rule' => function ($value, $context) {
                    if (empty($value)) {
                        return true; // 空はOK
                    }

                    if ($value instanceof \Laminas\Diactoros\UploadedFile) {
                        $type = $value->getClientMediaType();
                        \Cake\Log\Log::debug("📦 attachment mime type = {$type}");
                        return $type === 'application/pdf';
                    }

                    return false;
                },
                'message' => 'PDF以外はアップロードできません'
            ]);

            // ↓ この下に追加
            $validator
                ->add('announce_start_date', 'customDateRange', [
                    'rule' => function ($value, $context) {
                        $from = $value;
                        $to = $context['data']['announce_end_date'] ?? null;

                        if ($from && $to) {
                            return new \DateTime($from) <= new \DateTime($to);
                        }
                        // ここは両方必須前提なので直接比較
                        return new \DateTime($from) < new \DateTime($to);
                    },
                    'message' => '掲載開始日は掲載終了日より前の日付を指定してください。'
                ]);
        $validator->remove('disp_no');
        return $validator;
    }
}