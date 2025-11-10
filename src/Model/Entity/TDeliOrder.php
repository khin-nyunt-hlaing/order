<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * TDeliOrder Entity
 *
 * @property int $deli_order_id
 * @property string|null $user_id
 * @property int|null $term_id
 * @property string|null $order_status
 * @property string|null $del_flg
 * @property string|null $create_user
 * @property \Cake\I18n\DateTime|null $create_date
 * @property string|null $update_user
 * @property \Cake\I18n\DateTime|null $update_date
 *
 * @property \App\Model\Entity\MTerm $m_term
 */
class TDeliOrder extends Entity
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
        'deli_order_id' => true,
        'user_id' => true,
        'term_id' => true,
        'order_status' => true,
        'del_flg' => true,
        'create_user' => true,
        'create_date' => true,
        'update_user' => true,
        'update_date' => true,
        'm_term' => true,
    ];
}
