<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * MAnnounceDivFixture
 */
class MAnnounceDivFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public string $table = 'm_announce_div';
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'announce_div' => '75673928-0384-4b7c-a062-8b623839c49f',
                'announce_div_name' => 'Lorem ipsum dolor ',
                'disp_no' => 1.5,
                'del_flg' => '',
                'create_user' => 'Lor',
                'create_date' => '2025-07-23 11:31:43',
                'update_user' => 'Lor',
                'update_date' => '2025-07-23 11:31:43',
            ],
        ];
        parent::init();
    }
}
