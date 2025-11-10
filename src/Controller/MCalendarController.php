<?php
declare(strict_types=1);


namespace App\Controller;

use Cake\Log\Log;
use Cake\I18n\FrozenTime;

/**
 * MCalendar Controller
 *
 * @property \App\Model\Table\MCalendarTable $MCalendar
 */
class MCalendarController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */


 public function import()
{
    Log::debug('import() start');
    if (!$this->request->is('post')) {
        Log::debug('POSTではない');
        return;
    }
    $file = $this->request->getData('calendar_file');
    if (!$file) {
        Log::error('ファイルオブジェクトが取得できませんでした');
        $this->Flash->error('ファイルを選択してください。');
        return $this->redirect(['action' => 'import']);
    }

    // UploadedFileInterfaceか確認
    if (!method_exists($file, 'getClientFilename') || !method_exists($file, 'getStream')) {
        Log::error('アップロードファイルオブジェクトが期待した型ではありません: ' . gettype($file));
        $this->Flash->error('ファイルの形式が不正です。');
        return $this->redirect(['action' => 'import']);
    }

    $filename = $file->getClientFilename();
    Log::debug('ファイル名: ' . var_export($filename, true));
    $error = $file->getError();
    Log::debug('file->getError(): ' . var_export($error, true));
    $size = $file->getSize();
    Log::debug('file->getSize(): ' . var_export($size, true));

    if ($error !== UPLOAD_ERR_OK || $size === 0) {
        Log::error('アップロードエラーまたはファイルサイズ0');
        $this->Flash->error('有効なCSVファイルを選択してください。');
        return $this->redirect(['action' => 'import']);
    }

    // tmpPath取得
    $tmpPath = null;
    try {
        $stream = $file->getStream();
        if ($stream && method_exists($stream, 'getMetadata')) {
            $tmpPath = $stream->getMetadata('uri');
            Log::debug('tmpPath: ' . var_export($tmpPath, true));
        } else {
            Log::error('getStream() か getMetadata メソッドが使えません');
        }
    } catch (\Throwable $e) {
        Log::error('getStream/getMetadata 例外: ' . $e->getMessage());
    }

    if (!$tmpPath || !is_string($tmpPath) || !file_exists($tmpPath)) {
        Log::error('一時ファイルパス無効または存在しません: ' . var_export($tmpPath, true));
        $this->Flash->error('ファイルを読み込めませんでした。');
        return $this->redirect(['action' => 'import']);
    }

    $handle = fopen($tmpPath, 'r');
    if ($handle === false) {
        Log::error('fopenに失敗: ' . $tmpPath);
        $this->Flash->error('ファイルを開けませんでした。');
        return $this->redirect(['action' => 'import']);
    }
    Log::debug('fopen 成功');

    // ヘッダー行読み取り
    $header = fgetcsv($handle);
    if ($header === false) {
        Log::error('ヘッダー読み込み失敗');
        fclose($handle);
        $this->Flash->error('CSVヘッダーが読み込めませんでした。');
        return $this->redirect(['action' => 'import']);
    }
    $header = array_map(fn($h) => preg_replace('/^\xEF\xBB\xBF/', '', $h), $header);
    Log::debug('ヘッダー: ' . print_r($header, true));

    $importedDates = [];
    $rows = [];

    while (($row = fgetcsv($handle)) !== false) {
        if (count($row) !== count($header)) {
            Log::debug('row カラム数不一致 スキップ: ' . print_r($row, true));
            continue;
        }
        $assoc = array_combine($header, $row);
        if (!isset($assoc['calendar_date'])) {
            Log::debug('assoc に calendar_date キーなし スキップ: ' . print_r($assoc, true));
            continue;
        }
        $raw = trim($assoc['calendar_date']);
        if ($raw === '') {
            Log::debug('calendar_date 空 スキップ');
            continue;
        }
        $dateObj = \DateTime::createFromFormat('Y/n/j', $raw);
        if (!$dateObj) {
            Log::debug('日付フォーマット不一致 スキップ: ' . $raw);
            continue;
        }
        $formatted = $dateObj->format('Y-m-d');
        $importedDates[] = $formatted;
        $rows[] = $assoc;
    }
    fclose($handle);
    Log::debug('importedDates count: ' . count($importedDates));

    if (empty($importedDates)) {
        $this->Flash->error('CSVファイルの取込に失敗しました。');
        return $this->redirect(['action' => 'import']);
    }

    // 同一年チェック
    $years = array_unique(array_map(fn($d) => (new \DateTime($d))->format('Y'), $importedDates));
    Log::debug('years found: ' . implode(', ', $years));
    if (count($years) !== 1) {
        $this->Flash->error('CSVファイルには複数の年が含まれています。1年分のみ対応しています。');
        return $this->redirect(['action' => 'import']);
    }

    $year = (int)$years[0];
    $startDate = new \DateTime("$year-01-01");
    $endDate   = new \DateTime("$year-12-31");
    Log::debug('期待範囲 start: ' . $startDate->format('Y-m-d') . ', end: ' . $endDate->format('Y-m-d'));

    $expectedDates = [];
    $interval = new \DateInterval('P1D');
    $period = new \DatePeriod($startDate, $interval, (clone $endDate)->modify('+1 day'));
    foreach ($period as $d) {
        $expectedDates[] = $d->format('Y-m-d');
    }
    Log::debug('expectedDates count: ' . count($expectedDates));

    $missingDates = array_diff($expectedDates, $importedDates);
    Log::debug('missingDates count: ' . count($missingDates));
    if (!empty($missingDates)) {
        Log::error('不足日例: ' . implode(', ', array_slice($missingDates, 0, 5)));
        $this->Flash->error('日付が不足しています。');
        return $this->redirect(['action' => 'import']);
    }

    // ここから保存処理など
    foreach ($rows as $row) {
        $dateObj = \DateTime::createFromFormat('Y/n/j', trim($row['calendar_date']));
        if (!$dateObj) {
            continue;
        }

        $strHolidayFlg = trim($row['holiday_flg'] ?? '');

        if ($strHolidayFlg === '' || !ctype_digit($strHolidayFlg)) {
            $this->Flash->error('祝日フラグは0か1で入力してくださいa');
            return $this->redirect(['action' => 'import']);
        }

        $holidayFlg = (int)$strHolidayFlg;

        $identity = $this->Authentication->getIdentity();
                        Log::debug('🔑 identifier: ' . print_r($identity, true));
                        $createUser = $identity ? $identity->get('user_id') : 'system';
                        Log::debug('createUser=' . var_export($createUser, true));

        $formattedDate = $dateObj->format('Y-m-d');

        // 既存レコードの確認
        $existing = $this->MCalendar->find()
            ->where(['calendar_date' => $formattedDate])
            ->first();

        if ($existing) {
            // 更新処理
            $entity = $this->MCalendar->patchEntity($existing, [
                // 'calendar_date' => $formattedDate,
                'holiday_flg' => $holidayFlg,
                'del_flg'     => 0,
                'create_user' => $createUser,
                'update_user' => $createUser,
                'update_date' => FrozenTime::now(),
            ]);
        } else {
            // 新規作成
            $entity = $this->MCalendar->newEntity([
                'calendar_date' => $formattedDate,
                'holiday_flg'   => $holidayFlg,
                'del_flg'       => 0,
                'update_user'   => $createUser,
                'update_date'   => FrozenTime::now(),
                'create_user'   => $createUser,
                'create_date'   => FrozenTime::now(),
            ]);
        }

        if ($entity->getErrors()) {
            $this->Flash->error('祝日フラグは0か1で入力してください');
            return $this->redirect(['action' => 'import']);
        }

        if (!$this->MCalendar->save($entity)) {
            Log::error('保存失敗: ' . print_r($entity->toArray(), true));
        }
    }

    $this->Flash->success('取込成功');
    return $this->redirect(['action' => 'import']);
}

}
