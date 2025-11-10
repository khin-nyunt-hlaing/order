<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * MAuth Entity
 *
 * @property string|null $user_id
 * @property string|null $menu_id
 * @property string|null $use_div
 * @property string|null $create_user
 * @property \Cake\I18n\DateTime|null $create_date
 * @property string|null $update_user
 * @property \Cake\I18n\DateTime|null $update_date
 */
class MAuth extends Entity
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
        'menu_id' => true,
        'use_div' => true,
        'create_user' => true,
        'create_date' => true,
        'update_user' => true,
        'update_date' => true,
    ];
}
