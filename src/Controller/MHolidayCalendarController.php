<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\AppController;
use Cake\I18n\FrozenTime;
use Cake\Datasource\Exception\RecordNotFoundException;

class MHolidayCalendarController extends AppController
{
    public function index()
    {
        $table = $this->fetchTable('MCalendar');

        // ログインユーザー取得
        $identity  = $this->request->getAttribute('identity');
        $loginUser = $identity ? $identity->get('user_id') : 'system';

        // 一覧（休日のみ）
        $mCalendars = $table
            ->find()
            ->select([
                'calendar_date',
                'holiday_name',
                'holiday_flg',
                'weekday' => 'DATENAME(WEEKDAY, calendar_date)'
            ])
            ->where([
                'holiday_flg' => '1',
                'del_flg'     => '0'
            ])
            ->order(['calendar_date' => 'ASC']);

        $this->set(compact('mCalendars'));

        // 表示のみ
        if (!$this->request->is('post')) {
            return;
        }

        $action = $this->request->getData('action');

        $success = false;

        if ($action === 'delete') {
            $success = $this->deleteHoliday($table);
        } else {
            $success = $this->saveHoliday($table, $loginUser);
        }

        // ✅ 成功時のみリダイレクト
        if ($success) {
            return $this->redirect(['action' => 'index']);
        }

        // ❗ エラー時はそのまま画面再表示（入力値保持）
        return;
    }

    /**
     * 休日登録・更新
     */
    private function saveHoliday($table, string $loginUser): bool
    {
        $data = $this->request->getData();

        $year = $data['holiday_year'] ?? '';
        $mmdd = $data['holiday_mmdd'] ?? '';
        $name = $data['holiday_name'] ?? '';

        // 日付形式チェック
        if (!preg_match('/^\d{4}$/', $year) || !preg_match('/^\d{4}$/', $mmdd)) {
            $this->Flash->error('日付が不正です。');
            return false;
        }

        $month = (int)substr($mmdd, 0, 2);
        $day   = (int)substr($mmdd, 2, 2);

        // 実在日付チェック
        if (!checkdate($month, $day, (int)$year)) {
            $this->Flash->error('日付が不正です。');
            return false;
        }

        $calendarDate = sprintf('%04d-%02d-%02d', $year, $month, $day);

        try {
            $entity = $table->get($calendarDate);
        } catch (RecordNotFoundException $e) {
            $entity = $table->newEmptyEntity();
            $entity->calendar_date = $calendarDate;
            $entity->create_user  = $loginUser;
            $entity->create_date  = FrozenTime::now();
            $entity->del_flg      = '0';
        }

        $entity->holiday_flg  = '1';
        $entity->holiday_name = $name;
        $entity->update_user  = $loginUser;
        $entity->update_date  = FrozenTime::now();

        if ($table->save($entity)) {
            $this->Flash->success('休日を登録しました。');
            return true;
        }

        $this->Flash->error('休日の登録に失敗しました。');
        return false;
    }

    /**
     * 休日削除（物理削除）
     */
    private function deleteHoliday($table): bool
    {
        $selected = $this->request->getData('select');

        if (empty($selected)) {
            $this->Flash->error('削除対象を選択してください。');
            return false;
        }

        foreach (array_keys($selected) as $calendarDate) {
            try {
                $entity = $table->get($calendarDate);
                $table->delete($entity);
            } catch (RecordNotFoundException $e) {
                continue;
            }
        }

        $this->Flash->success('選択した休日を削除しました。');
        return true;
    }
}
