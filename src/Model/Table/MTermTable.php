<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * MTerm Model
 *
 * @method \App\Model\Entity\MTerm newEmptyEntity()
 * @method \App\Model\Entity\MTerm newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\MTerm> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\MTerm get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\MTerm findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\MTerm patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\MTerm> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\MTerm|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\MTerm saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\MTerm>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MTerm>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MTerm>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MTerm> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MTerm>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MTerm>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MTerm>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MTerm> deleteManyOrFail(iterable $entities, array $options = [])
 */
class MTermTable extends AppTable
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

        $this->setTable('m_term');
        $this->setDisplayField('term_id');
        
        // M_TERM 1レコードに対し、T_DELI_ORDER 1レコード（ログインユーザー分）
            $this->hasMany('TDeliOrder', [
                'className' => 'TDeliOrder',
                'foreignKey' => 'term_id',
                'bindingKey' => 'term_id'
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
        $validator = parent::validationDefault($validator);

        $validator->remove('disp_no');

        $validator
            ->allowEmptyString('term_id');

        $validator
            ->date('start_date')
            ->allowEmptyDate('start_date', 'このフィールドを入力してください。','create')
            ->notEmptyDate('start_date');

        $validator
            ->date('end_date')
            ->allowEmptyDate('end_date', 'このフィールドを入力してください。')
            ->add('end_date', 'period7days', [
            'rule' => function ($endValue, $context) {
                // start / end_date の両方が入力されていることを確認
                $data = $context['data'] ?? [];
                if (empty($data['start_date']) || empty($endValue)) {
                    return false; // どちらか欠けていればNG
                }

                try {
                    // 文字列でも DateTime でも来る可能性を吸収
                    $start = $data['start_date'] instanceof \DateTimeInterface
                        ? \DateTimeImmutable::createFromInterface($data['start_date'])
                        : new \DateTimeImmutable((string)$data['start_date']);

                    $end = $endValue instanceof \DateTimeInterface
                        ? \DateTimeImmutable::createFromInterface($endValue)
                        : new \DateTimeImmutable((string)$endValue);

                    // 日付のみで比較（時刻の差分に影響されないよう 00:00:00 固定）
                    $start0 = $start->setTime(0, 0, 0);
                    $end0   = $end->setTime(0, 0, 0);

                    // 差分（日数）が 6 であれば“両端含め7日間”
                    $days = $start0->diff($end0)->days;
                    return $days === 6;
                } catch (\Throwable $e) {
                    return false;
                }
            },
            'message' => '開始日と終了日は、7日間になるように設定してください。',
        ]);

        $validator
            ->date('entry_start_date')
            ->allowEmptyDate('entry_start_date', 'このフィールドを入力してください。');

        $validator
            ->date('add_deadline_date')
            ->allowEmptyDate('add_deadline_date', 'このフィールドを入力してください。');

        $validator
            ->dateTime('upd_deadline_date')
            ->allowEmptyDateTime('upd_deadline_date', 'このフィールドを入力してください。');

        
        return $validator;
    }
    public function buildRules(RulesChecker $rules): RulesChecker
{
    // 既存のルールがあればこの上にある想定

    $rules->add(function ($entity, $options) {
        $start = $entity->get('start_date');
        $end   = $entity->get('end_date');
        if (!$start || !$end) {
            return false;
        }

        try {
            $startDt = $start instanceof \DateTimeInterface
                ? \DateTimeImmutable::createFromInterface($start)
                : new \DateTimeImmutable((string)$start);

            $endDt = $end instanceof \DateTimeInterface
                ? \DateTimeImmutable::createFromInterface($end)
                : new \DateTimeImmutable((string)$end);

            // start <= end を許容
            return $startDt->setTime(0,0,0) <= $endDt->setTime(0,0,0);
        } catch (\Throwable $e) {
            return false;
        }
    }, 'startBeforeOrEqualEnd', [
        'errorField' => 'end',
        'message' => '終了日は開始日以降の日付を指定してください。'
    ]);

    return $rules;
}
/**
 * テーブルT の Aカラム / Bカラム の日付について、
 * テーブルC の 日付カラム と一致する行の ホリデーカラム(0/1) を取得する。
 *
 * @param \DateTimeInterface|string $Aカラム テーブルTのAカラムの値
 * @param \DateTimeInterface|string $Bカラム テーブルTのBカラムの値
 * @return array{aHoliday: bool, bHoliday: bool}
 */
public function getHolidayFlagsForAB($addDeadline, $updDeadline): array
{
    $toYmd = static function ($v): string {
        if ($v instanceof \DateTimeInterface) {
            return $v->format('Y-m-d');
        }
        $dt = new \DateTimeImmutable((string)$v);
        return $dt->format('Y-m-d');
    };

    try {
        $aYmd = $toYmd($addDeadline); // ← テーブルTのaddDeadline
        $bYmd = $toYmd($updDeadline); // ← テーブルTのupdDeadline
    } catch (\Throwable $e) {
        return ['aHoliday' => false, 'bHoliday' => false];
    }

    // テーブルC を取得
    $MCalendarTable = $this->getTableLocator()->get('MCalendarTable');

    // テーブルC の 日付カラム に addDeadline/updDeadline が含まれるかを確認
    $rows = $MCalendarTable->find()
        ->select(['calendar_date', 'holiday_flg'])
        ->where(['calendar_date IN' => [$aYmd, $bYmd]])
        ->enableHydration(false)
        ->all()
        ->toList();

    // 日付カラムをキーにホリデーカラムをマップ化
    $map = [];
    foreach ($rows as $r) {
        $ymd = (new \DateTimeImmutable((string)$r['calendar_date']))->format('Y-m-d');
        $map[$ymd] = (int)$r['holiday_flg'];
    }

    return [
        'aHoliday' => isset($map[$aYmd]) ? ($map[$aYmd] === 1) : false,
        'bHoliday' => isset($map[$bYmd]) ? ($map[$bYmd] === 1) : false,
    ];
    }
}
