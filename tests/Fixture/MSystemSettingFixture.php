<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * MSystemSettingFixture
 */
class MSystemSettingFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public string $table = 'm_system_setting';
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'system_id' => 1,
                'deli_min_chk' => 1.5,
                'deli_chg_chk' => 1.5,
                'deli_req_chk' => 1.5,
                'update_user' => 'Lor',
                'update_date' => '2025-07-17 18:52:13',
            ],
        ];
        parent::init();
    }
}
