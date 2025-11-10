<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * TDeliOrderDtl Entity
 *
 * @property int $deli_order_id
 * @property int $deli_order_dtl_id
 * @property \Cake\I18n\Date|null $term_date
 * @property int|null $delivery_id
 * @property int|null $quantity
 * @property int|null $keep_qty
 * @property string|null $create_user
 * @property \Cake\I18n\DateTime|null $create_date
 * @property string|null $update_user
 * @property \Cake\I18n\DateTime|null $update_date
 */
class TDeliOrderDtl extends Entity
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
        'term_date' => true,
        'delivery_id' => true,
        'quantity' => true,
        'keep_qty' => true,
        'create_user' => true,
        'create_date' => true,
        'update_user' => true,
        'update_date' => true,
    ];
}
