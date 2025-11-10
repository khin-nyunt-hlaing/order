<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * MSystemSetting Entity
 *
 * @property int $system_id
 * @property string|null $deli_min_chk
 * @property string|null $deli_chg_chk
 * @property string|null $deli_req_chk
 * @property string|null $update_user
 * @property \Cake\I18n\DateTime|null $update_date
 */
class MSystemSetting extends Entity
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
        'deli_min_chk' => true,
        'deli_chg_chk' => true,
        'deli_req_chk' => true,
        'update_user' => true,
        'update_date' => true,
    ];
}
