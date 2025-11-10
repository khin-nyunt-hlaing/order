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
   public function index()
{
    $now = FrozenTime::now();

    // 🔹 検索条件
    $from = $this->request->getQuery('add_deadline_from');
    $to = $this->request->getQuery('add_deadline_to');
    $conditions = ['del_flg' => '0'];
    if (!empty($from)) $conditions['add_deadline_date >='] = $from;
    if (!empty($to)) $conditions['add_deadline_date <='] = $to;

    // 🔹 削除処理（POST時のみ）
        if ($this->request->is('post')) {
            $action = $this->request->getData('action');
            $selected = array_keys(array_filter($this->request->getData('select') ?? []));

            if ($action === 'search') {// 🔹 検索条件
                $conditions = ['del_flg' => '0'];

                $from = $this->request->getData('add_deadline_from');
                $to = $this->request->getData('add_deadline_to');
                
                if (!empty($from)) $conditions['add_deadline_date >='] = $from;
                if (!empty($to)) $conditions['add_deadline_date <='] = $to;

                // 🔹 抽出・ページネーション
                $MTerm = $this->MTerm->find()
                    ->where($conditions)
                    ->order(['start_date' => 'DESC']);  

                // 🔹 抽出・ページネーション
                    $Count = $MTerm->count();
                    $MTerm  = $this->paginate($MTerm);

                    $this->set(compact('MTerm', 'Count', 'from', 'to'));
            }

            // 🔹 追加
            if ($action === 'add') {
                return $this->redirect(['action' => 'add']);
            }

            // 🔹 更新
                $action = $this->request->getData('action');
                $rawSelect = $this->request->getData('select') ?? [];
                $selected = array_keys(array_filter($rawSelect));


            Log::debug("🔍 action = $action");
            // Log::debug("📌 rawSelect = " . print_r($rawSelect, true));
            Log::debug("📌 selected = " . print_r($selected, true));

            if ($action === 'edit') {
                if (count($selected) === 1) {
                    $id = $selected[0];
                    try {
                        $mTerm = $this->MTerm->find()
                            ->where(['term_id' => $id, 'del_flg' => '0'])
                            ->firstOrFail();

                        Log::debug('✅ 編集対象のMTermデータ: ' . print_r($mTerm->toArray(), true));

                        return $this->redirect(['action' => 'edit', $id]);
                    } catch (\Cake\Datasource\Exception\RecordNotFoundException $e) {
                        $this->Flash->error("指定された献立期間（ID: {$id}）は存在しません。");
                        return $this->redirect(['action' => 'index']);
                    }
                } elseif (count($selected) > 1) {
                    $this->Flash->error('更新は1件のみ選択可能です。');
                    return $this->redirect(['action' => 'index']);
                } else {
                    $this->Flash->error('献立期間が選択されていません。');
                    return $this->redirect(['action' => 'index']);
                }
            }

            if ($action === 'upload') {
                return $this->redirect(['action' => 'upload']);
            }


            if ($action === 'delete') {
                if (!empty($selected)) {
                    $TDeliOrder =  $this->fetchTable('TDeliOrder');
                    $usedIds = $TDeliOrder->find()
                                            ->select(['term_id'])
                                            ->where([
                                                'term_id IN' => $selected,
                                                'del_flg' => 0, // 使用中だけを対象
                                            ])
                                            ->distinct(['term_id'])
                                            ->enableHydration(false)
                                            ->all()
                                            ->extract('term_id')
                                            ->toList();

                    $cannotDelete = array_values(array_intersect($selected, $usedIds));
                    $canDelete    = array_values(array_diff($selected, $cannotDelete));

                    if (!empty($cannotDelete)) {
                        if (count($selected) === 1) {
                            // $this->Flash->error('この献立期間は TDeliOrder で使用中のため削除できません。term_id=' . $cannotDelete[0]);
                            $this->Flash->error('配食発注で使用されている為、削除できません。');
                            return $this->redirect(['action' => 'index']);
                        }
                         else {
                            // $this->Flash->warning('一部の献立期間は TDeliOrder で使用中のため削除できません: ' . implode(', ', $cannotDelete));
                            $this->Flash->error('配食発注で使用されている為、削除できません。');
                            return $this->redirect(['action' => 'index']);
                        }
                    }

                    $terms = $this->MTerm->find()
                        ->where(['term_id IN' => $selected, 'del_flg' => 0])
                        ->all();
                    foreach ($terms as $term) {
                        $term->del_flg = 1;
                        $term->update_user = $this->request->getAttribute('identity')->get('user_id'); // ←追加
                        $this->MTerm->save($term);
                    }
                    $this->Flash->success('選択された献立期間を削除しました。');
                } else {
                    $this->Flash->error('献立期間が選択されていません。');
                }
            }
        }
    // 🔹 抽出・ページネーション
        $query = $this->MTerm->find()->where($conditions)->order(['start_date' => 'DESC']);
        $Count = $query->count();

        // 🔽 ページネーション設定を上書き（最大300件）
        $this->paginate = [
            'limit' => 300,
            'maxLimit' => 300
        ];

        $MTerm = $this->paginate($query);

    // 🔹 ステータス判定
    foreach ($MTerm as $mterm) {
        $entryStart = new FrozenTime($mterm->entry_start_date);
        $addDeadline = new FrozenTime($mterm->add_deadline_date);
        $updDeadline = new FrozenTime($mterm->upd_deadline_date);

        if ($now < $entryStart) {
            $mterm->status_message = '入力受付前';
            $mterm->status_code = 0;
        } elseif ($now <= $addDeadline) {
            $mterm->status_message = '受付中';
            $mterm->status_code = 1;
        } elseif ($now <= $updDeadline) {
            $mterm->status_message = '更新可能期間';
            $mterm->status_code = 2;
        } else {
            $mterm->status_message = '入力期限外';
            $mterm->status_code = 3;
        }

        // ログ（必要に応じて）
        // Log::debug("[期限確認] term_id={$mterm->term_id}, 判定結果={$mterm->status_code}:{$mterm->status_message}");
    }
    

    $this->set(compact('MTerm', 'Count', 'now'));
    
}
//追加処理
public function add(){
    $mTerm = $this->MTerm->newEmptyEntity();
    $calendarTable = $this->fetchTable('MCalendar');

    if ($this->request->is('post')) {
        $data = $this->request->getData();

        try {
            // 日付チェック・整形
            $start = new \DateTime($data['start_date']);
            $end = new \DateTime($data['end_date']);
            $entryStart = new \DateTime($data['entry_start_date']);
            $addDeadline = new \DateTime($data['add_deadline_date']);

            // 献立期間開始日
            if (empty($data['start_date'])) {
                    $this->Flash->error('献立期間開始日を入力してください。');
                    $this->set(compact('mTerm'));
                    $this->set('mode', 'add');
                    return $this->render('add_edit');
            }
            // 献立期間終了日
            if (empty($data['end_date'])) {
                    $this->Flash->error('献立期間終了日を入力してください。');
                    $this->set(compact('mTerm'));
                    $this->set('mode', 'add');
                    return $this->render('add_edit');
            }
            // 受付開始日チェック
            if (empty($data['entry_start_date'])) {
                    $this->Flash->error('受付開始日を入力してください。');
                    $this->set(compact('mTerm'));
                    $this->set('mode', 'add');
                    return $this->render('add_edit');
            }
            // 新規締切日チェック
            if (empty($data['add_deadline_date'])) {
                    $this->Flash->error('新規締切日を入力してください。');
                    $this->set(compact('mTerm'));
                    $this->set('mode', 'add');
                    return $this->render('add_edit');
            }
            if ($start > $end) {
               $this->Flash->error('献立期間開始日は献立期間終了日より前の日付を指定してください。');
                $this->set(compact('mTerm'));
                $this->set('mode', 'add');
                return $this->render('add_edit');
            }
            if ($addDeadline > $end) {
                $this->Flash->error('新規締切日が不正です。');
                $this->set(compact('mTerm'));
                $this->set('mode', 'add');
                return $this->render('add_edit');
            }
            $earliest = $start;
            if ($end < $earliest)        { $earliest = $end; }
            if ($addDeadline < $earliest){ $earliest = $addDeadline; }
            if ($entryStart > $earliest) {
                $this->Flash->error('受付開始日が不正です。');
                $this->set(compact('mTerm'));
                $this->set('mode', 'add');
                return $this->render('add_edit');
            }
            if ($entryStart >= $addDeadline) {
                $this->Flash->error('受付開始日は新規締切日より前の日付を指定してください。');
                $this->set(compact('mTerm'));
                $this->set('mode', 'add');
                return $this->render('add_edit');
            }

            //重複処理
            $startDateStr = $start->format('Y-m-d H:i:s');
            $endDateStr = $end->format('Y-m-d H:i:s');

            $overlapQuery = $this->MTerm->find()
            ->where(['del_flg' => '0'])
            ->andWhere(function ($exp) use ($startDateStr, $endDateStr) {
                // NOT(既存.end < 新.start OR 既存.start > 新.end)
                return $exp->not(
                    $exp->or([
                        'MTerm.end_date <'   => $startDateStr,
                        'MTerm.start_date >' => $endDateStr,
                    ])
                );
            });

            // 編集の場合、自分自身は除外
            if (!empty($mTerm->term_id)) {
                $overlapQuery->andWhere(['MTerm.term_id !=' => $mTerm->term_id]);
            }

            $overlapCount = $overlapQuery->count();

            if ($overlapCount > 0) {
                $this->set(compact('mTerm'));
                $this->set('mode', 'add');
                $this->render('add_edit');
                $this->Flash->error('献立期間開始日～献立期間終了日が他データと重複しています。');
                //return $this->redirect($this->referer());
                return $this->render('add_edit');
            }

            // upd_deadline_date 自動設定（終了日の7日前 16:59）
            $updDeadline = (clone $end)->modify('-7 days')->setTime(16, 59);
            $data['upd_deadline_date'] = $updDeadline->format('Y-m-d H:i:s');

            // ユーザー情報セット
            $userId = $this->request->getAttribute('identity')->get('user_id');
            $data['create_user'] = $userId;
            $data['update_user'] = $userId;
            $data['del_flg'] = '0';

            // 祝日補正
                foreach (['add_deadline_date', 'upd_deadline_date'] as $field) {
                    if (!empty($data[$field])) {
                        try {
                            $date = new FrozenDate($data[$field]);
                            while (true) {
                                $calendar = $calendarTable->find()
                                    ->where(['calendar_date' => $date->format('Y-m-d H:i:s')])
                                    ->first();
                                if ($calendar && $calendar->holiday_flg === '1') {
                                    $date = $date->modify('-1 day');
                                } else {
                                    break;
                                }
                            }
                            $data[$field] = $date->format('Y-m-d H:i:s');
                        } catch (\Exception $e) {
                            $errors[] = "{祝日変換:$field}の日付形式が不正です。";
                            continue;
                        }
                    }
                }

                // 日付型変換
                foreach (['start_date', 'end_date', 'entry_start_date', 'add_deadline_date', 'upd_deadline_date'] as $field) {
                    if (!empty($data[$field])) {
                        try {
                            $data[$field] = new FrozenTime($data[$field]);
                        } catch (\Exception $e) {
                            $errors[] = "日付型変換:{$field} の日付形式が不正です。";
                            continue;
                        }
                    }
                }

            // エンティティに反映
            $mTerm = $this->MTerm->patchEntity($mTerm, $data);

            // 1) ここでフォームエラーがあれば保存せずに再表示
            if ($mTerm->hasErrors()) {
                $this->Flash->error('入力内容にエラーがあります。');
                $this->set(compact('mTerm'));
                $this->set('mode', 'add');
                return $this->render('add_edit');
            }

            // 2) 保存は1回だけ
            if (!$this->MTerm->save($mTerm)) {
                $this->set(compact('mTerm'));
                $this->set('mode', 'add');
                $this->render('add_edit');
                return $this->Flash->error('MTermの保存に失敗しました。');
            }

            // 3) 生成されたIDはエンティティから取得（SCOPE_IDENTITYは不要）
            $lastInsertId = $mTerm->term_id ?? null;

            $this->Flash->success('登録しました。');
            return $this->redirect(['action' => 'index']);


        } catch (\Exception $e) {
                $this->set(compact('mTerm'));
                $this->set('mode', 'add');
                $this->render('add_edit');
            $this->Flash->error('システムエラーです。登録に失敗しました。');
        }
    }

    $this->set(compact('mTerm'));
    $this->set('mode', 'add');
    $this->render('add_edit');
}
// 更新処理
public function edit($id = null)
{
    $mTerm = $this->MTerm->get($id);
    $calendarTable = $this->fetchTable('MCalendar');

    if ($this->request->is(['post', 'put', 'patch'])) {
        $data = $this->request->getData();

        // 新規締切日チェック
        if (empty($data['add_deadline_date'])) {
            $this->Flash->error('新規締切日を入力してください。');
            //return $this->redirect($this->referer());
        }

        try {
            // 🔸 日付整形
            $start = new \DateTime($data['start_date']);
            $end = new \DateTime($data['end_date']);
            $entryStart = new \DateTime($data['entry_start_date']);
            $addDeadline = new \DateTime($data['add_deadline_date']);

            // 献立期間開始日
            if (empty($data['start_date'])) {
                    $this->Flash->error('献立期間開始日を入力してください。');
                    $this->set(compact('mTerm'));
                    $this->set('mode', 'add');
                    return $this->render('add_edit');
            }
            // 献立期間終了日
            if (empty($data['end_date'])) {
                    $this->Flash->error('献立期間終了日を入力してください。');    
                    $this->set(compact('mTerm'));
                    $this->set('mode', 'add');
                    return $this->render('add_edit');
            }
            // 新規締切日チェック
            if (empty($data['add_deadline_date'])) {
                    $this->Flash->error('新規締切日を入力してください。');
                    $this->set(compact('mTerm'));
                    $this->set('mode', 'add');
                    return $this->render('add_edit');
            }
            if ($start > $end) {
                $this->Flash->error('献立期間開始日は献立期間終了日より前の日付を指定してください。');
                $this->set(compact('mTerm'));
                $this->set('mode', 'add');
                return $this->render('add_edit');
            }
            if ($addDeadline > $end) {
                $this->Flash->error('新規締切日が不正です。');
                $this->set(compact('mTerm'));
                $this->set('mode', 'add');
                return $this->render('add_edit');
            }
            $earliest = $start;
            if ($end < $earliest)        { $earliest = $end; }
            if ($addDeadline < $earliest){ $earliest = $addDeadline; }
            if ($entryStart > $earliest) {
                $this->Flash->error('受付開始日が不正です。');
                $this->set(compact('mTerm'));
                $this->set('mode', 'add');
                return $this->render('add_edit');
            }
            if ($entryStart >= $addDeadline) {
                $this->Flash->error('受付開始日は新規締切日より前の日付を指定してください。');
                $this->set(compact('mTerm'));
                $this->set('mode', 'add');
                return $this->render('add_edit');
            }

            // 🔸 重複チェック（他のレコードとstart_dateまたはend_dateが一致）
            $startStr = $start->format('Y-m-d H:i:s');
            $endStr = $end->format('Y-m-d H:i:s');

            $overlapCount = $this->MTerm->find()
                ->where(['del_flg' => '0'])
                ->andWhere(function ($exp) use ($startStr, $endStr) {
                    return $exp->or([
                        'MTerm.start_date' => $startStr,
                        'MTerm.end_date' => $endStr,
                    ]);
                })
                ->andWhere(['MTerm.term_id !=' => $id]) // 自分自身は除外
                ->count();

            if ($overlapCount > 0) {
                $this->set(compact('mTerm'));
                $this->set('mode', 'add');
                $this->render('add_edit');
                return $this->Flash->error('献立期間開始日～献立期間終了日が他データと重複しています。');
            }

            // 🔸 upd_deadline_date を自動設定（end_date の7日前 16:59）
            $updDeadline = (clone $end)->modify('-7 days')->setTime(16, 59);
            $data['upd_deadline_date'] = $updDeadline->format('Y-m-d H:i:s');

            // 🔸 更新ユーザーを上書き
            $loginUserId = $this->request->getAttribute('identity')->get('user_id');
            $data['update_user'] = $loginUserId;

            // 祝日補正
                foreach (['add_deadline_date', 'upd_deadline_date'] as $field) {
                    if (!empty($data[$field])) {
                        try {
                            $date = new FrozenDate($data[$field]);
                            while (true) {
                                $calendar = $calendarTable->find()
                                    ->where(['calendar_date' => $date->format('Y-m-d H:i:s')])
                                    ->first();
                                if ($calendar && $calendar->holiday_flg === '1') {
                                    $date = $date->modify('-1 day');
                                } else {
                                    break;
                                }
                            }
                            $data[$field] = $date->format('Y-m-d H:i:s');
                        } catch (\Exception $e) {
                            $errors[] = "{祝日変換:$field}の日付形式が不正です。";
                            continue;
                        }
                    }
                }

                // 日付型変換
                foreach (['start_date', 'end_date', 'entry_start_date', 'add_deadline_date', 'upd_deadline_date'] as $field) {
                    if (!empty($data[$field])) {
                        try {
                            $data[$field] = new FrozenTime($data[$field]);
                        } catch (\Exception $e) {
                            $errors[] = "日付型変換:{$field} の日付形式が不正です。";
                            continue;
                        }
                    }
                }

            // 🔸 入力内容をエンティティに反映
            $mTerm = $this->MTerm->patchEntity($mTerm, $data);

            // 1) ここでフォームエラーがあれば保存せずに再表示
            if ($mTerm->hasErrors()) {
                $this->Flash->error('入力内容にエラーがあります。');
                // ← ここで return して add_edit を再表示（POST→同画面）
                $this->set(compact('mTerm'));
                $this->set('mode', 'edit');
                return $this->render('add_edit');
            }

            if ($this->MTerm->save($mTerm)) {
                $this->Flash->success('更新しました。');
                return $this->redirect(['action' => 'index']);
            }

            \Cake\Log\Log::debug('🛑 バリデーションエラー: ' . print_r($mTerm->getErrors(), true));
            $this->Flash->error('更新に失敗しました。');

        } catch (\Exception $e) {
            $this->Flash->error('システムエラーです。登録に失敗しました。');
        }
    }

    $this->set(compact('mTerm'));
    $this->set('mode', 'edit');
    $this->render('add_edit');
}
// ファイル取込 日付データを登録する
public function upload()
{
    try{
        
        if ($this->request->is('post')) {
            $file = $this->request->getData('attachment');
            Log::debug('📎 受け取ったファイル: ' . print_r($file, true));

            if ($file && $file->getError() === UPLOAD_ERR_OK) {
                // 拡張子チェック
                    $allowedExtensions = ['csv', 'txt'];
                    $ext = strtolower(pathinfo($file->getClientFilename(), PATHINFO_EXTENSION));

                    if (!in_array($ext, $allowedExtensions)) {
                        $this->Flash->error('CSVまたはテキストファイルのみアップロード可能です。');
                        return $this->redirect(['action' => 'upload']);
                    }

                // MIMEタイプチェック
                    $allowedMimeTypes = [
                        'text/csv',
                        'application/vnd.ms-excel', // 古いCSV形式
                        'text/plain',               // テキストファイル（.txt）など
                    ];

                    $mimeType = $file->getClientMediaType();
                    if (!in_array($mimeType, $allowedMimeTypes)) {
                        $this->Flash->error('不正なファイル形式です。CSVまたはテキストファイルを選んでください。');
                        return $this->redirect(['action' => 'upload']);
                    }

                // ファイル読み込み＆処理（既存処理）
                $tmpPath = $file->getStream()->getMetadata('uri');
                $csvData = file_get_contents($tmpPath);
                $lines = explode("\n", trim($csvData));
                $header = str_getcsv(array_shift($lines));
                $header = array_map(fn($h) => preg_replace('/^\xEF\xBB\xBF/u', '', $h), $header);

                $saved = 0;
                $errors = [];
                $entities = [];
                $mTermTable = $this->fetchTable('MTerm');
                $calendarTable = $this->fetchTable('MCalendar');
                $termPeriods = [];
                $overlapCount = 0;

                // 1行ずつ読み込み、期間チェックまで行う
                foreach ($lines as $rowNum => $line) {
                    $rowNum += 2; // CSVの行数（ヘッダー + 1ベース）
                    if (trim($line) === '') continue;

                    $values = str_getcsv($line);
                    if (count($values) !== count($header)) {
                        $errors[] = "{$rowNum}行目：カラム数が一致しません。";
                        continue;
                    }

                    $data = array_combine($header, $values);

                    // BOM補正
                    if (isset($data["﻿start_date"])) {
                        $data["start_date"] = $data["﻿start_date"];
                        unset($data["﻿start_date"]);
                    }

                    // 初期値補完
                    $data['create_user'] = $this->Authentication->getIdentity()->get('user_id');
                    $data['update_user'] = $this->Authentication->getIdentity()->get('user_id');
                    $data['del_flg'] = 0;

                   // 空欄チェック & 日付形式チェック
                    if (empty($data['start_date'])) {
                        $this->Flash->error('献立期間開始日が不正です。');
                        $mTerm = $mTermTable->newEmptyEntity();
                        $this->set(compact('mTerm'));
                        $this->set('mode', 'add');
                        return $this->render('upload');
                    }
                    try {
                        $start = new FrozenTime($data['start_date']);
                    } catch (\Exception $e) {
                        $this->Flash->error('献立期間開始日は日付で設定してください。');
                        $mTerm = $mTermTable->newEmptyEntity();
                        $this->set(compact('mTerm'));
                        $this->set('mode', 'add');
                        return $this->render('upload');
                    }

                    if (empty($data['end_date'])) {
                        $this->Flash->error('献立期間終了日が不正です。');
                        $mTerm = $mTermTable->newEmptyEntity();
                        $this->set(compact('mTerm'));
                        $this->set('mode', 'add');
                        return $this->render('upload');
                    }
                    try {
                        $end = new FrozenTime($data['end_date']);
                    } catch (\Exception $e) {
                        $this->Flash->error('献立期間終了日は日付で設定してください。');
                        $mTerm = $mTermTable->newEmptyEntity();
                        $this->set(compact('mTerm'));
                        $this->set('mode', 'add');
                        return $this->render('upload');
                    }

                    if (empty($data['entry_start_date'])) {
                        $this->Flash->error('受付開始日が不正です。');
                        $mTerm = $mTermTable->newEmptyEntity();
                        $this->set(compact('mTerm'));
                        $this->set('mode', 'add');
                        return $this->render('upload');
                    }
                    try {
                        $entryStart = new FrozenTime($data['entry_start_date']);
                    } catch (\Exception $e) {
                        $this->Flash->error('受付開始日は日付で設定してください。');
                        $mTerm = $mTermTable->newEmptyEntity();
                        $this->set(compact('mTerm'));
                        $this->set('mode', 'add');
                        return $this->render('upload');
                    }

                    if (empty($data['add_deadline_date'])) {
                        $this->Flash->error('新規締切日が不正です。');
                        $mTerm = $mTermTable->newEmptyEntity();
                        $this->set(compact('mTerm'));
                        $this->set('mode', 'add');
                        return $this->render('upload');
                    }
                    try {
                        $addDeadline = new FrozenTime($data['add_deadline_date']);
                    } catch (\Exception $e) {
                        $this->Flash->error('新規締切日は日付で設定してください。');
                        $mTerm = $mTermTable->newEmptyEntity();
                        $this->set(compact('mTerm'));
                        $this->set('mode', 'add');
                        return $this->render('upload');
                    }

                    if (empty($data['upd_deadline_date'])) {
                        $this->Flash->error('修正締切日が不正です。');
                        $mTerm = $mTermTable->newEmptyEntity();
                        $this->set(compact('mTerm'));
                        $this->set('mode', 'add');
                        return $this->render('upload');
                    }
                    try {
                        $updDeadline = !empty($data['upd_deadline_date']) ? new FrozenTime($data['upd_deadline_date']) : null;
                    } catch (\Exception $e) {
                        $this->Flash->error('修正締切日は日付で設定してください。');
                        $mTerm = $mTermTable->newEmptyEntity();
                        $this->set(compact('mTerm'));
                        $this->set('mode', 'add');
                        return $this->render('upload');
                    }



                    // 整合性チェック（論理エラー）
                    if ($start > $end) {
                        $this->Flash->error('献立期間開始日は献立期間終了日より前の日付を指定してください。');
                        $mTerm = $mTermTable->newEmptyEntity();
                        $this->set(compact('mTerm'));
                        $this->set('mode', 'add');
                        return $this->render('upload');
                    }

                    if ($addDeadline > $end) {
                        $this->Flash->error('新規締切日は献立期間終了日より前に設定してください。');
                        $mTerm = $mTermTable->newEmptyEntity();
                        $this->set(compact('mTerm'));
                        $this->set('mode', 'add');
                        return $this->render('upload');
                    }

                    $earliest = min($start, $end, $addDeadline);

                    if ($entryStart > $earliest) {
                        $this->Flash->error('受付開始日は献立期間開始日よりも前に設定してください。');
                        $mTerm = $mTermTable->newEmptyEntity();
                        $this->set(compact('mTerm'));
                        $this->set('mode', 'add');
                        return $this->render('upload');
                    }

                    if ($entryStart >= $addDeadline) {
                        $this->Flash->error('受付開始日は新規締切日より前の日付を指定してください。');
                        $mTerm = $mTermTable->newEmptyEntity();
                        $this->set(compact('mTerm'));
                        $this->set('mode', 'add');
                        return $this->render('upload');
                    }

                    if ($entryStart >= $start) {
                        $this->Flash->error('受付開始日は献立期間開始日よりも前の日付を指定してください。');
                        $mTerm = $mTermTable->newEmptyEntity();
                        $this->set(compact('mTerm'));
                        $this->set('mode', 'add');
                        return $this->render('upload');
                    }

                    if ($addDeadline >= $start) {
                        $this->Flash->error('新規締切日は献立期間開始日よりも前の日付で指定してください。');
                        $mTerm = $mTermTable->newEmptyEntity();
                        $this->set(compact('mTerm'));
                        $this->set('mode', 'add');
                        return $this->render('upload');
                    }

                    if (!empty($updDeadline) && $updDeadline >= $start) {
                        $this->Flash->error('修正締切日は献立期間開始日よりも前の日付を指定してください。');
                        $mTerm = $mTermTable->newEmptyEntity();
                        $this->set(compact('mTerm'));
                        $this->set('mode', 'add');
                        return $this->render('upload');
                    }

                    if ($overlapCount > 0) {
                        $this->Flash->error('献立期間開始日～献立期間終了日が他データと重複しています。');
                        $mTerm = $mTermTable->newEmptyEntity();
                        $this->set(compact('mTerm'));
                        $this->set('mode', 'add');
                        return $this->render('upload');
                    }



                    // 期間配列に行番号含めて追加（後で重複チェック用）
                    $termPeriods[] = ['start' => $start, 'end' => $end, 'row' => $rowNum];

                    // 祝日補正
                    foreach (['add_deadline_date', 'upd_deadline_date'] as $field) {
                        if (!empty($data[$field])) {
                            try {
                                $date = new FrozenDate($data[$field]);
                                while (true) {
                                    $calendar = $calendarTable->find()
                                        ->where(['calendar_date' => $date->format('Y-m-d H:i:s')])
                                        ->first();
                                    if ($calendar && $calendar->holiday_flg === '1') {
                                        $date = $date->modify('-1 day');
                                    } else {
                                        break;
                                    }
                                }
                                $data[$field] = $date->format('Y-m-d H:i:s');
                            } catch (\Exception $e) {
                                $errors[] = "{$field}の日付形式が不正です。";
                                continue 2;
                            }
                        }
                    }

                    // 日付型変換
                    foreach (['start_date', 'end_date', 'entry_start_date', 'add_deadline_date', 'upd_deadline_date'] as $field) {
                        if (!empty($data[$field])) {
                            try {
                                $data[$field] = new FrozenTime($data[$field]);
                            } catch (\Exception $e) {
                                $errors[] = "{$field} の日付形式が不正です。";
                                continue 2;
                            }
                        }
                    }

                    // DBの重複チェック（他データとの重複）
                    $startStr = $start->format('Y-m-d H:i:s');
                    $endStr = $end->format('Y-m-d H:i:s');
                    // DBの重複チェック（他データとの重複）
                    $overlapCount = $mTermTable->find()
                    ->where([
                        'del_flg' => 0,
                        'OR' => [
                            ['start_date <=' => $start, 'end_date >=' => $start],
                            ['start_date <=' => $end, 'end_date >=' => $end],
                            ['start_date >=' => $start, 'end_date <=' => $end],
                        ],
                    ])
                    ->count();

                    if ($overlapCount > 0) {
                        $errors[] = "献立期間開始日～献立期間終了日が他データと重複しています。";
                        continue;
                    }


                    // 保存用エンティティ作成して一時的に保持
                    $entities[$rowNum] = $mTermTable->newEmptyEntity();
                    $entities[$rowNum] = $mTermTable->patchEntity($entities[$rowNum], $data);
                }

                // ここでCSV内の期間重複チェックを一括実施
                for ($i = 0; $i < count($termPeriods); $i++) {
                    for ($j = $i + 1; $j < count($termPeriods); $j++) {
                        $a = $termPeriods[$i];
                        $b = $termPeriods[$j];
                        if (!($a['end'] < $b['start'] || $a['start'] > $b['end'])) {
                            $this->Flash->error("献立期間開始日～献立期間終了日が他データと重複しています。");
                            return $this->redirect($this->referer());
                        }
                    }
                }
                // CSV内の期間重複チェック（隣接も重複とみなす）
                for ($i = 0; $i < count($termPeriods); $i++) {
                    for ($j = $i + 1; $j < count($termPeriods); $j++) {
                        $a = $termPeriods[$i];
                        $b = $termPeriods[$j];
                        // 非重複：a.end < b.start  または  a.start > b.end
                        $nonOverlap = ($a['end'] < $b['start']) || ($a['start'] > $b['end']);
                        if (!$nonOverlap) {
                            $this->Flash->error("CSV内で献立期間が重複しています。");
                            return $this->redirect($this->referer());
                        }
                    }
                }

                // エラーがあればリダイレクト
                if (!empty($errors)) {
                    $uniqueErrors = array_unique($errors);
                    foreach ($uniqueErrors as $err) {
                        $this->Flash->error($err);
                    }
                    return $this->redirect($this->referer());
                }

                // 重複チェックOKなら保存
                $saved = 0;
                foreach ($entities as $entity) {
                    if ($mTermTable->save($entity)) {
                        $saved++;
                    } else {
                        $errors[] = "保存処理に失敗しました。";
                    }
                }

                if (!empty($errors)) {
                    $uniqueErrors = array_unique($errors);
                    foreach ($uniqueErrors as $err) {
                        $this->Flash->error($err);
                    }
                    return $this->redirect($this->referer());
                }

                $this->Flash->success("{$saved}件のデータを取込しました。");
                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error('取込ファイルが設定されていません。');
        }
    } catch (\Exception $e) {
        $this->Flash->error('システムエラーです。ファイル取込に失敗しました。');
    }
}

}
