<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class AppTable extends Table
{
     public function initialize(array $config): void
    {
        parent::initialize($config);

        // 全テーブル共通の Timestamp 設定
        $this->addBehavior('Timestamp', [
            'events' => [
                'Model.beforeSave' => [
                    'create_date' => 'new',
                    'update_date' => 'always',
                ],
            ],
        ]);
    }
    public function validationDefault(Validator $validator): Validator
    {
        $validator = parent::validationDefault($validator);


        $validator
            ->requirePresence('disp_no')
            ->notEmptyString('disp_no', 'このフィールドを入力してください。')
            ->add('disp_no', 'digits18', [
                'rule' => ['custom', '/^\d{1,18}$/'],
                'message' => '入力可能桁数を超えています。'
            ]);
            
        $validator
            ->scalar('del_flg')               // 型：文字列
            ->maxLength('del_flg', 1)         // 1文字制限
            ->allowEmptyString('del_flg')     // 空白OKを追加
            ->inList('del_flg', ['0', '1'], '0または1を入力してください');

        $validator
            ->scalar('create_user')
            ->maxLength('create_user', 15)
            ->allowEmptyString('create_user');

        $validator
            ->dateTime('create_date')
            ->allowEmptyDateTime('create_date');

        $validator
            ->scalar('update_user')
            ->maxLength('update_user', 15)
            ->allowEmptyString('update_user');

        $validator
            ->dateTime('update_date')
            ->allowEmptyDateTime('update_date');

        return $validator;
    }

    
}