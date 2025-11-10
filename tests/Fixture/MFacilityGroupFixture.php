<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * MUserGroupFixture
 */
class MUserGroupFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public string $table = 'm_facility_group';
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'group_id' => '',
                'group_name' => 'Lorem ipsum dolor sit amet',
                'disp_no' => 1.5,
                'del_flg' => '',
                'create_user' => 'Lor',
                'create_date' => '2025-07-10 14:58:10',
                'update_user' => 'Lor',
                'update_date' => '2025-07-10 14:58:10',
            ],
        ];
        parent::init();
    }
}
