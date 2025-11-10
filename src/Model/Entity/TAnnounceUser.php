<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * TAnnounceUser Entity
 *
 * @property int $ANNOUNCE_ID
 * @property string $USER_ID
 * @property string|null $CREATE_USER
 * @property \Cake\I18n\DateTime|null $CREATE_DATE
 * @property string|null $UPDATE_USER
 * @property \Cake\I18n\DateTime|null $UPDATE_DATE
 */
class TAnnounceUser extends Entity
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
        'create_user' => true,
        'create_date' => true,
        'update_user' => true,
        'update_date' => true,
        'announce_id' => true,
        'user_id' => true,
    ];
}
