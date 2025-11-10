<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;
use Authentication\PasswordHasher\DefaultPasswordHasher;


/**
 * MUser Entity
 *
 * @property string $USER_ID
 * @property string|null $USER_NAME
 * @property string|null $PASSWORD
 * @property string|null $QUESTION
 * @property string|null $ANSWER
 * @property string|null $STATUS
 * @property string $USE_SERVICE_ID
 * @property string|null $USE_PATTERN_ID
 * @property string|null $READ_TIME
 * @property string|null $DISP_NO
 * @property string|null $DEL_FLG
 * @property string|null $CREATE_USER
 * @property \Cake\I18n\DateTime|null $CREATE_DATE
 * @property string|null $UPDATE_USER
 * @property \Cake\I18n\DateTime|null $UPDATE_DATE
 * @property string|null $address
 */
class MUser extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'user_id' => true,
        'user_name' => true,
        'password' => true,
        'question' => true,
        'answer' => true,
        'status' => true,
        'use_service_id' => true,
        'use_pattern_id' => true,
        'read_time' => true,
        'disp_no' => true,
        'del_flg' => true,
        'create_user' => true,
        'create_date' => true,
        'update_user' => true,
        'update_date' => true,
    ];
        protected function _setPassword(string $password): string
    {
        return (new DefaultPasswordHasher())->hash($password);
    }
}
