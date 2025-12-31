<?php
declare(strict_types=1);

namespace App\Model\Entity;
use Cake\I18n\Date;
use Cake\ORM\Entity;

/**
 * MTerm Entity
 *
 * @property int|null $term_id
 * @property \Cake\I18n\Date|null $start_date
 * @property \Cake\I18n\Date|null $end_date
 * @property \Cake\I18n\Date|null $entry_start_date
 * @property \Cake\I18n\Date|null $add_deadline_date
 *
 * @property string|null $del_flg
 * @property string|null $create_user
 * @property \Cake\I18n\DateTime|null $create_date
 * @property string|null $update_user
 * @property \Cake\I18n\DateTime|null $update_date
 */
class MTerm extends Entity
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
        'start_date' => true,
        'end_date' => true,
        'entry_start_date' => true,
        'add_deadline_date' => true,
        'upd_deadline_monday' => true,
        'upd_deadline_tue'    => true,
        'upd_deadline_wed'    => true,
        'upd_deadline_thu'    => true,
        'upd_deadline_fri'    => true,
        'upd_deadline_sat'    => true,
        'upd_deadline_sun'    => true,
        'del_flg' => true,
        'create_user' => true,
        'create_date' => true,
        'update_user' => true,
        'update_date' => true,
    ];
    /**
     * 仮想プロパティ: upd_deadline_date
     * 既存コード互換のため
     */
    protected function _getUpdDeadlineDate(): ?\DateTimeImmutable
    {
        if (empty($this->start_date)) {
            return null;
        }

        // 開始日の曜日（0=日〜6=土）
        $w = (int)$this->start_date->format('w');

        $map = [
            1 => $this->upd_deadline_monday,
            2 => $this->upd_deadline_tue,
            3 => $this->upd_deadline_wed,
            4 => $this->upd_deadline_thu,
            5 => $this->upd_deadline_fri,
            6 => $this->upd_deadline_sat,
            0 => $this->upd_deadline_sun,
        ];

        $d = $map[$w] ?? null;
        if (!$d) {
            return null;
        }

        if ($d instanceof Date) {
            return new \DateTimeImmutable($d->format('Y-m-d'));
        }

        return new \DateTimeImmutable((string)$d);
    }
}