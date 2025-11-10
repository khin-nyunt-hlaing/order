<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * TAnnounce Entity
 *
 * @property int $announce_id
 * @property int|null $announce_div
 * @property \Cake\I18n\Date|null $announce_start_date
 * @property \Cake\I18n\Date|null $announce_end_date
 * @property string|null $announce_title
 * @property string|null $announce_text
 * @property string|null $temp_filename1
 * @property string|null $temp_filename2
 * @property string|null $temp_filename3
 * @property string|null $temp_filename4
 * @property string|null $temp_filename5
 * @property string|null $visibility
 * @property string|null $del_flg
 * @property string|null $create_user
 * @property \Cake\I18n\DateTime|null $create_date
 * @property string|null $update_user
 * @property \Cake\I18n\DateTime|null $update_date
 */
class TAnnounce extends Entity
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
        'announce_div' => true,
        'announce_start_date' => true,
        'announce_end_date' => true,
        'announce_title' => true,
        'announce_text' => true,
        'temp_filename1' => true,
        'temp_filename2' => true,
        'temp_filename3' => true,
        'temp_filename4' => true,
        'temp_filename5' => true,
        'visibility' => true,
        'del_flg' => true,
        'create_user' => true,
        'create_date' => true,
        'update_user' => true,
        'update_date' => true,
    ];
}
