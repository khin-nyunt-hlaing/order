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
    public string $table = 'm_user_group';
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'user_group_id' => 'fe130a72-2f16-4b59-9fc8-a2dcea55fa93',
                'user_name' => 'Lorem ipsum dolor sit amet',
                'disp_no' => 1.5,
                'del_flg' => '',
                'create_user' => 'Lor',
                'create_date' => '2025-07-18 19:12:13',
                'update_user' => 'Lor',
                'update_date' => '2025-07-18 19:12:13',
            ],
        ];
        parent::init();
    }
}
