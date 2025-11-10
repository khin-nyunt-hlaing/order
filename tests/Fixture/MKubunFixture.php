<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * MKubunFixture
 */
class MKubunFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public string $table = 'm_kubun';
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'kubun_cd' => 'c6637c6d-210a-46ff-bb4c-a1c79ec756d7',
                'kubun_value' => '',
                'kubun_name' => 'Lorem ipsum dolor sit amet',
                'disp_no' => 1.5,
                'del_flg' => '',
                'create_user' => 'Lor',
                'create_date' => '2025-06-26 16:53:32',
                'update_user' => 'Lor',
                'update_date' => '2025-06-26 16:53:32',
            ],
        ];
        parent::init();
    }
}
