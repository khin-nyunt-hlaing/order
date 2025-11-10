<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * TFoodOrderFix Entity
 *
 * @property int $food_order_id
 * @property string|null $user_id
 * @property \Cake\I18n\Date|null $order_date
 * @property \Cake\I18n\Date|null $deli_req_date
 * @property \Cake\I18n\Date|null $deli_shedule_date
 * @property string|null $food_id
 * @property int|null $order_quantity
 * @property string|null $order_status
 * @property string|null $del_flg
 * @property string|null $create_user
 * @property \Cake\I18n\DateTime|null $create_date
 * @property string|null $update_user
 * @property \Cake\I18n\DateTime|null $update_date
 */
class TFoodOrderFix extends Entity
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
        'food_order_id'  => true,
        'user_id' => true,
        'order_date' => true,
        'deli_req_date' => true,
        'deli_shedule_date' => true,
        'food_id' => true,
        'order_quantity' => true,
        'order_status' => true,
        'del_flg' => true,
        'create_user' => true,
        'create_date' => true,
        'update_user' => true,
        'update_date' => true,
    ];
}
