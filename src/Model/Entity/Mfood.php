<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Mfood Entity
 *
 * @property string $food_id
 * @property string $food_name
 * @property string $category_id
 * @property string|null $food_specification
 * @property int|null $tax_included_price
 * @property string|null $disp_no
 * @property string|null $del_flg
 * @property string|null $create_user
 * @property \Cake\I18n\DateTime|null $create_date
 * @property string|null $update_user
 * @property \Cake\I18n\DateTime|null $update_date
 */
class Mfood extends Entity
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
        'food_id' => true,
        'food_name' => true,
        'category_id' => true,
        'food_specification' => true,
        'tax_included_price' => true,
        'disp_no' => true,
        'del_flg' => true,
        'create_user' => true,
        'create_date' => true,
        'update_user' => true,
        'update_date' => true,
    ];
}
