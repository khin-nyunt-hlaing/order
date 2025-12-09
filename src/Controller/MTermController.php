<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Database\Expression\QueryExpression;
use Cake\ORM\Query;
use App\Model\Table\TDeliOrderTable;
use Cake\Controller\Controller; // ←8/18追加分
use Cake\I18n\FrozenTime;
use Cake\I18n\FrozenDate;
use Cake\Log\Log;
use Cake\I18n\Date;
use \Exception;

/**
 * 献立期間編集コントローラー   MTerm Controller
 *
 * @property \App\Model\Table\MTermTable $MTerm
 */
class MTermController extends AppController
{
    /**
     * 土日祝を考慮して前営業日にずらす
     */
    private function adjustBusinessDayBackward(\DateTime $date, $calendarTable)
    {
        while (true) {
            $calendar = $calendarTable->find()
                ->where(['calendar_date' => $date->format('Y-m-d H:i:s')])
                ->first();

            // 土日 または 祝日
            $week = (int)$date->format('w');
            if ($week === 0 || $week === 6 || ($calendar && $calendar->holiday_flg === '1')) {
                $date->modify('-1 day'); // 1日前に戻す
            } else {
                break;
            }
        }

        return $date;
    }
    private function adjustHolidayOnlyBackward(\DateTime $date, $calendarTable)
    {
        while (true) {
            $calendar = $calendarTable->find()
                ->where(['calendar_date' => $date->format('Y-m-d H:i:s')])
                ->first();

            // 祝日のみ前倒し
            if ($calendar && $calendar->holiday_flg === '1') {
                $date->modify('-1 day');
            } else {
                break;
            }
        }
        return $date;
    }
    private function renderAddWithDeadline($mTerm, $data)
    {
        $this->set('mTerm', $mTerm);
        $this->set('mode', 'add');

        if (isset($data['add_deadline_date'])) {
            $this->set('add_deadline_date', $data['add_deadline_date']);
        } elseif (!empty($mTerm->add_deadline_date)) {
            $this->set('add_deadline_date', $mTerm->add_deadline_date->format('Y-m-d'));
        }

        // ▼ ★修正締切日7項目を保持
        $updDates = [];
        foreach (['monday','tue','wed','thu','fri','sat','sun'] as $d) {
            $key = "upd_deadline_" . $d;
            $updDates["upd_deadline_$d"] = $data[$key] ?? '';
        }

        $this->set('updDates', $updDates);

        return $this->render('add_edit');
    }

    private function renderEditWithDeadline($mTerm, $data)
    {
        $this->set('mTerm', $mTerm);
        $this->set('mode', 'edit');

        // ▼ add_deadline_date の保持
        if (isset($data['add_deadline_date'])) {
            $this->set('add_deadline_date', $data['add_deadline_date']);
        } elseif (!empty($mTerm->add_deadline_date)) {
            $this->set('add_deadline_date', $mTerm->add_deadline_date->format('Y-m-d'));
        }

        // ▼ 修正締切日（7項目）を保持する
        $updDates = [];
        foreach (['monday','tue','wed','thu','fri','sat','sun'] as $d) {
            $key = "upd_deadline_" . $d;

            if (isset($data[$key])) {
                // POST入力値
                $updDates["upd_deadline_$d"] = $data[$key];
            } else {
                // DB値
                $updDates["upd_deadline_$d"] =
                    !empty($mTerm->$key) ? $mTerm->$key : '';
            }
        }

        $this->set('updDates', $updDates);

        return $this->render('add_edit');
    }

    // 一覧
    public function index()
{
    $now = FrozenTime::now();
    $conditions = ['del_flg' => '0'];

    // ============================
    // GETパラメータ取得
    // ============================
    $startFrom = $this->request->getQuery('start_from');
    $startTo   = $this->request->getQuery('start_to');

    $addFrom   = $this->request->getQuery('add_from');
    $addTo     = $this->request->getQuery('add_to');

    $updFrom   = $this->request->getQuery('upd_from');
    $updTo     = $this->request->getQuery('upd_to');

    $completed = $this->request->getQuery('completed');  // 完了受付 ON/OFF


    // ▼ 献立日
    if (!empty($startFrom))  $conditions['start_date >='] = $startFrom;
    if (!empty($startTo))    $conditions['start_date <='] = $startTo;

    // ▼ 新規締切日
    if (!empty($addFrom))    $conditions['add_deadline_date >='] = $addFrom;
    if (!empty($addTo))      $conditions['add_deadline_date <='] = $addTo;

    // ▼ 変更締切日（7項目 OR 検索）
    if (!empty($updFrom) || !empty($updTo)) {

        $conditions[] = function(QueryExpression $exp) use ($updFrom, $updTo) {

            $cols = [
                'upd_deadline_monday',
                'upd_deadline_tue',
                'upd_deadline_wed',
                'upd_deadline_thu',
                'upd_deadline_fri',
                'upd_deadline_sat',
                'upd_deadline_sun'
            ];

            $orList = [];

            foreach ($cols as $c) {
                if (!empty($updFrom)) {
                    $orList[] = ["$c >=" => $updFrom];
                }
                if (!empty($updTo)) {
                    $orList[] = ["$c <=" => $updTo];
                }
            }

            return $exp->or($orList);
        };
    }


            // デフォルトは「受付中」「準備中」だけ表示
        if (empty($completed)) {

            $today = FrozenDate::today();

            $conditions[] = function(QueryExpression $exp) use ($today) {

                return $exp->or([
                    // ▼ 新規締切日が未来なら「受付中」
                    ['add_deadline_date >=' => $today],
                    ['add_deadline_date IS' => null],  // データがない場合も表示

                    // ▼ 修正締切日のどれかが未来なら「準備中扱い」
                    ['upd_deadline_monday >=' => $today],
                    ['upd_deadline_tue >='    => $today],
                    ['upd_deadline_wed >='    => $today],
                    ['upd_deadline_thu >='    => $today],
                    ['upd_deadline_fri >='    => $today],
                    ['upd_deadline_sat >='    => $today],
                    ['upd_deadline_sun >='    => $today],

                    // NULL は未来扱い
                    ['upd_deadline_monday IS' => null],
                    ['upd_deadline_tue IS'    => null],
                    ['upd_deadline_wed IS'    => null],
                    ['upd_deadline_thu IS'    => null],
                    ['upd_deadline_fri IS'    => null],
                    ['upd_deadline_sat IS'    => null],
                    ['upd_deadline_sun IS'    => null],
                ]);
            };
        }


    // ============================
    // POST（検索・編集・削除など）
    // ============================
    if ($this->request->is('post')) {

        $action   = $this->request->getData('action');
        $selected = array_keys(array_filter($this->request->getData('select') ?? []));

        // 🔍 検索 → GET に変換
        if ($action === 'search') {

            $q = [];
            foreach (['start_from','start_to','add_from','add_to','upd_from','upd_to','completed'] as $f) {
                $v = $this->request->getData($f);
                if ($v !== null && $v !== '') {
                    $q[$f] = $v;
                }
            }

            return $this->redirect(['action' => 'index', '?' => $q]);
        }

        // 新規
        if ($action === 'add') {
            return $this->redirect(['action' => 'add']);
        }

        // 編集
        if ($action === 'edit') {

            if (count($selected) !== 1) {
                $this->Flash->error('更新は1件のみ選択してください。');
                return $this->redirect(['action' => 'index']);
            }

            return $this->redirect(['action' => 'edit', $selected[0]]);
        }

        // 削除
        if ($action === 'delete') {

            if (empty($selected)) {
                $this->Flash->error('献立期間が選択されていません。');
                return $this->redirect(['action' => 'index']);
            }

            $TDeliOrder = $this->fetchTable('TDeliOrder');

            $usedIds = $TDeliOrder->find()
                ->select(['term_id'])
                ->where(['term_id IN' => $selected, 'del_flg' => 0])
                ->distinct(['term_id'])
                ->all()
                ->extract('term_id')
                ->toList();

            if (!empty($usedIds)) {
                $this->Flash->error('発注入力があるので削除できません。');
                return $this->redirect(['action' => 'index']);
            }

            foreach ($selected as $id) {
                $entity = $this->MTerm->get($id);
                $this->MTerm->delete($entity);
            }

            $this->Flash->success('削除しました。');
            return $this->redirect(['action' => 'index']);
        }
    }


    // ============================
    // 一覧取得
    // ============================
    $query = $this->MTerm->find()
        ->where($conditions)
        ->order(['start_date' => 'DESC']);

    $Count = $query->count();
    $this->paginate = ['limit' => 300, 'maxLimit' => 300];
    $MTerm = $this->paginate($query);


    // ============================
    // ステータス判定（表示用）
    // ============================
    foreach ($MTerm as $t) {

        $entryStart  = new FrozenTime($t->entry_start_date);
        $addDeadline = new FrozenTime($t->add_deadline_date);

        // ※ upd_deadline_date は廃止したため最も未来の締切を判定
        $updDates = [
            $t->upd_deadline_monday,
            $t->upd_deadline_tue,
            $t->upd_deadline_wed,
            $t->upd_deadline_thu,
            $t->upd_deadline_fri,
            $t->upd_deadline_sat,
            $t->upd_deadline_sun
        ];

        // NULL は除外して一番未来の値をとる
        $validUpd = array_filter($updDates);
        $maxUpd = !empty($validUpd) ? max($validUpd) : null;

        $updDeadline = $maxUpd ? new FrozenTime($maxUpd) : null;

        if ($now < $entryStart) {
            $t->status_message = '入力受付前';
        } elseif ($now <= $addDeadline) {
            $t->status_message = '受付中';
        } elseif ($updDeadline && $now <= $updDeadline) {
            $t->status_message = '更新可能期間';
        } else {
            $t->status_message = '入力期限外';
        }
    }


    // ============================
    // viewへ渡す
    // ============================
    $this->set(compact(
        'MTerm', 'Count', 'now',
        'startFrom', 'startTo',
        'addFrom', 'addTo',
        'updFrom', 'updTo',
        'completed'
    ));
}

    // 追加処理
    public function add()
{
    $mTerm = $this->MTerm->newEmptyEntity();
    $calendarTable = $this->fetchTable('MCalendar');

    if ($this->request->is('post')) {

        $data = $this->request->getData();

        try {

            // ================================
            // 基本日付の生成
            // ================================
            $start      = new \DateTime($data['start_date']);  // 月曜日
            $end        = new \DateTime($data['end_date']);    // 日曜日
            $entryStart = new \DateTime($data['entry_start_date']);

            // ▼ 開始＞終了チェック
            if ($start > $end) {
                $this->Flash->error('献立期間開始日は献立期間終了日より前の日付を指定してください。');
                return $this->renderAddWithDeadline($mTerm, $data);
            }

            if ((int)$start->format('w') !== 1) {
                $this->Flash->error('献立日は月曜日を指定してください。');
                return $this->renderAddWithDeadlin($mTerm, $data);
            }
            // ================================
            // ▼ 6ヶ月チェック（警告のみ）
            // ================================
            $sixMonthsLater = (new \DateTime('today'))->modify('+6 months');
            if ($start > $sixMonthsLater) {
                $this->Flash->warning('6か月以降先の献立日が入力されています。');
            }

            // ================================
            // ▼ 月曜日チェック
            // ================================
            $today = new \DateTime('today');

            if ((int)$start->format('w') !== 1 || $start < $today) {
                $this->Flash->error('献立日が不正です。');
                return $this->renderAddWithDeadline($mTerm, $data);
            }

            // ================================
            // ▼ 新規締切日（-14日 → 土日祝前倒し）
            // ================================
            $addDeadline = (clone $start)->modify('-14 days');
            $addDeadline = $this->adjustHolidayOnlyBackward($addDeadline, $calendarTable);

            // ★★ ここでチェック：新規締切日 > 献立開始日ならエラー
            $entryStart = new \DateTime($data['entry_start_date']);
            if ($addDeadline > $start) {
                $this->Flash->error('新規締切日が不正です。');
                return $this->renderAddWithDeadline($mTerm, $data);
            }
            
            if ($entryStart > $start) {
                $this->Flash->error('受付開始日が不正です。');
                return $this->renderAddWithDeadline($mTerm, $data);
            }

            $data['add_deadline_date'] = $addDeadline->format('Y-m-d');

            // ================================
            // ▼ 修正締切日（曜日別 7項目）
            // upd_deadline_monday ～ sun に保存
            // ================================
            $updSave = [];

            foreach (['monday','tue','wed','thu','fri','sat','sun'] as $d) {

                // 入力項目名と一致させる
                $key = "upd_deadline_" . $d;

                if (empty($data[$key])) {
                    $updSave[$d] = null;
                    continue;
                }

                // 土日祝補正して保存
                $date = new \DateTime($data[$key]);
                $date = $this->adjustBusinessDayBackward($date, $calendarTable);

                $updSave[$d] = $date->format('Y-m-d');
            }

            // DB保存用
            $data['upd_deadline_monday'] = $updSave['monday'];
            $data['upd_deadline_tue']    = $updSave['tue'];
            $data['upd_deadline_wed']    = $updSave['wed'];
            $data['upd_deadline_thu']    = $updSave['thu'];
            $data['upd_deadline_fri']    = $updSave['fri'];
            $data['upd_deadline_sat']    = $updSave['sat'];
            $data['upd_deadline_sun']    = $updSave['sun'];

            // ================================
            // ▼ 受付開始日（献立日 -42日）
            // ================================
            $data['entry_start_date'] =
                (clone $start)->modify('-42 days')->format('Y-m-d');

            $startDateStr = $start->format('Y-m-d H:i:s');
            $endDateStr   = $end->format('Y-m-d H:i:s');

            $overlapQuery = $this->MTerm->find()
                ->where(['del_flg' => '0'])
                ->andWhere(function ($exp) use ($startDateStr, $endDateStr) {
                    return $exp->not(
                        $exp->or([
                            'MTerm.end_date <'   => $startDateStr,
                            'MTerm.start_date >' => $endDateStr,
                        ])
                    );
                });

            // 編集は自分自身を除外
            if (!empty($mTerm->term_id)) {
                $overlapQuery->andWhere(['MTerm.term_id !=' => $mTerm->term_id]);
            }

            if ($overlapQuery->count() > 0) {
                $this->Flash->error('献立期間が他データと重複しています。');
                return $this->renderAddWithDeadline($mTerm, $data);
            }

            // ================================
            // ▼ FrozenTime 変換
            // ================================
            $data['start_date']       = new FrozenTime($data['start_date']);
            $data['end_date']         = new FrozenTime($data['end_date']);
            $data['entry_start_date'] = new FrozenTime($data['entry_start_date']);
            $data['add_deadline_date'] = new FrozenTime($data['add_deadline_date']);

            // ================================
            // ▼ 共通項目
            // ================================
            $userId = $this->request->getAttribute('identity')->get('user_id');
            $data['create_user'] = $userId;
            $data['update_user'] = $userId;
            $data['del_flg']     = '0';

            // ================================
            // ▼ 保存処理
            // ================================
            $mTerm = $this->MTerm->patchEntity($mTerm, $data);

            if ($mTerm->hasErrors()) {
                $this->Flash->error('入力内容にエラーがあります。');
                return $this->renderAddWithDeadline($mTerm, $data);
            }

            if (!$this->MTerm->save($mTerm)) {
                $this->Flash->error('保存に失敗しました。');
                return $this->renderAddWithDeadline($mTerm, $data);
            }

            $this->Flash->success('登録しました。');

            $nextStart = (clone $start)->modify('+7 days')->format('Y-m-d');

            // 続けて登録モード
            return $this->redirect([
                'action' => 'add',
                '?' => [
                    'next_start' => $nextStart,
                    'continue'   => 1
                ]
            ]);

        } catch (\Exception $e) {

            $this->Flash->error('システムエラーです。登録に失敗しました。');
            return $this->renderAddWithDeadline($mTerm, $data);
        }
    }

    // ▼ 初期表示用
    $updDates = [
        'upd_deadline_monday' => '',
        'upd_deadline_tue'    => '',
        'upd_deadline_wed'    => '',
        'upd_deadline_thu'    => '',
        'upd_deadline_fri'    => '',
        'upd_deadline_sat'    => '',
        'upd_deadline_sun'    => '',
    ];

    $holidays = $calendarTable->find()
        ->select(['calendar_date'])
        ->where(['holiday_flg' => '1'])
        ->all()
        ->extract('calendar_date')
        ->map(fn($d) => $d->format('Y-m-d'))
        ->toList();

    $this->set(compact('mTerm', 'updDates', 'holidays'));
    $this->set('mode', 'add');
    return $this->render('add_edit');
}



// 更新処理
public function edit($id = null)
{
    $mTerm = $this->MTerm->get($id);
    $calendarTable = $this->fetchTable('MCalendar');

    if ($this->request->is(['post', 'put', 'patch'])) {

        $data = $this->request->getData();

        try {

            // ---------- 型変換 ----------
            $start = new \DateTime($data['start_date']);   // 献立開始日（月）
            $end   = new \DateTime($data['end_date']);     // 献立終了日（日）

            // ---------- 月曜日チェック ----------

            // ▼ 開始＞終了チェック
            if ($start > $end) {
                $this->Flash->error('献立期間開始日は献立期間終了日より前の日付を指定してください。');
                return $this->renderAddWithDeadlin($mTerm, $data);
            }
            

			
			// ================================
            // ▼ 6ヶ月チェック（警告のみ）
            // ================================
            $sixMonthsLater = (new \DateTime('today'))->modify('+6 months');
            if ($start > $sixMonthsLater) {
                $this->Flash->warning('6か月以降先の献立日が入力されています。');
            }
			
			// ---------- 月曜日チェック ----------    
            $today = new \DateTime('today');

            if ((int)$start->format('w') !== 1 || $start < $today) {
                $this->Flash->error('献立日が不正です。');
                return $this->renderEditWithDeadline($mTerm, $data);
            }
			
            // =====================================
            // ▼ 新規締切日（画面入力を優先）
            //    入力なしなら「-14日 → 土日祝前倒し」
            // =====================================
            if (!empty($data['add_deadline_date'])) {
                $addDeadline = new \DateTime($data['add_deadline_date']);
                $addDeadline = $this->adjustHolidayOnlyBackward($addDeadline, $calendarTable);
            } else {
                // 入力が空なら自動計算
                $addDeadline = (clone $start)->modify('-14 days');
                $addDeadline = $this->adjustBusinessDayBackward($addDeadline, $calendarTable);
            }

            // ★★ ここでチェック：新規締切日 > 献立開始日ならエラー
            $entryStart = new \DateTime($data['entry_start_date']);
            if ($addDeadline > $start) {
                $this->Flash->error('新規締切日が不正です。');
                return $this->renderEditWithDeadline($mTerm, $data);
            }
            
            if ($entryStart > $start) {
                $this->Flash->error('受付開始日が不正です。');
                return $this->renderEditWithDeadline($mTerm, $data);
            }

            // DB 保存用にフォーマット
            $data['add_deadline_date'] = $addDeadline->format('Y-m-d');

            // =====================================
            // ▼ 修正締切日（7項目を DB 保存）
            // =====================================
            $upd = [];

            foreach (['monday','tue','wed','thu','fri','sat','sun'] as $d) {

                $key = "upd_deadline_" . $d;

                if (empty($data[$key])) {
                    $upd[$d] = null;
                    continue;
                }

                $tmp = new \DateTime($data[$key]);
                $tmp = $this->adjustBusinessDayBackward($tmp, $calendarTable);

                $upd[$d] = $tmp->format('Y-m-d');
            }

            $data['upd_deadline_monday'] = $upd['monday'];
            $data['upd_deadline_tue']    = $upd['tue'];
            $data['upd_deadline_wed']    = $upd['wed'];
            $data['upd_deadline_thu']    = $upd['thu'];
            $data['upd_deadline_fri']    = $upd['fri'];
            $data['upd_deadline_sat']    = $upd['sat'];
            $data['upd_deadline_sun']    = $upd['sun'];

            // =====================================
            // ▼ 受付開始日（献立日 -42日）
            // =====================================
            if (!empty($data['entry_start_date'])) {
                $entryStart = new \DateTime($data['entry_start_date']);
            } else {
                $entryStart = (clone $start)->modify('-42 days');
            }


            $startDateStr = $start->format('Y-m-d H:i:s');
            $endDateStr   = $end->format('Y-m-d H:i:s');

            // 重複チェック
            $overlapQuery = $this->MTerm->find()
                ->where(['del_flg' => '0'])
                ->andWhere(function ($exp) use ($startDateStr, $endDateStr) {
                    return $exp->not(
                        $exp->or([
                            'MTerm.end_date <'   => $startDateStr,
                            'MTerm.start_date >' => $endDateStr,
                        ])
                    );
                });

            // add() では term_id がまだ無いので除外処理は不要だが書いてもOK
            if (!empty($mTerm->term_id)) {
                $overlapQuery->andWhere(['MTerm.term_id !=' => $mTerm->term_id]);
            }

            if ($overlapQuery->count() > 0) {
                $this->Flash->error('献立期間が他データと重複しています。');
                return $this->renderEditWithDeadline($mTerm, $data);
            }

            $data['entry_start_date'] = $entryStart->format('Y-m-d');   

            // 更新者
            $data['update_user'] = $this->request->getAttribute('identity')->get('user_id');

            // ---------- patch & save ----------
            $mTerm = $this->MTerm->patchEntity($mTerm, $data);

            if ($mTerm->hasErrors()) {
                $this->Flash->error('入力内容にエラーがあります。');
                return $this->renderEditWithDeadline($mTerm, $data);
            }

            if ($this->MTerm->save($mTerm)) {
                $this->Flash->success('更新しました。');
                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error('保存に失敗しました。');

        } catch (\Exception $e) {
            $this->Flash->error('システムエラーです。');
        }

        return $this->renderEditWithDeadline($mTerm, $data);
    }

    // ===========================
    // 初期表示（DB値をそのまま渡す）
    // ===========================
    $updDates = [
        'upd_deadline_monday' => $mTerm->upd_deadline_monday,
        'upd_deadline_tue'    => $mTerm->upd_deadline_tue,
        'upd_deadline_wed'    => $mTerm->upd_deadline_wed,
        'upd_deadline_thu'    => $mTerm->upd_deadline_thu,
        'upd_deadline_fri'    => $mTerm->upd_deadline_fri,
        'upd_deadline_sat'    => $mTerm->upd_deadline_sat,
        'upd_deadline_sun'    => $mTerm->upd_deadline_sun,
    ];

    $holidays = $calendarTable->find()
        ->select(['calendar_date'])
        ->where(['holiday_flg' => '1'])
        ->all()
        ->extract('calendar_date')
        ->map(fn($d) => $d->format('Y-m-d'))
        ->toList();

    $this->set(compact('mTerm','updDates','holidays'));
    $this->set('mode','edit');

    return $this->render('add_edit');
}
    
}
