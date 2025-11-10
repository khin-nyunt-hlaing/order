<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * MDeliveryFixture
 */
class MDeliveryFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public string $table = 'm_delivery';
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'delivery_id' => 1,
                'delivery_name' => 'Lorem ipsum dolor sit amet',
                'disp_no' => 1.5,
                'del_flg' => '',
                'create_user' => 'Lor',
                'create_date' => '2025-07-11 11:53:38',
                'update_user' => 'Lor',
                'update_date' => '2025-07-11 11:53:38',
            ],
        ];
        parent::init();
    }
}
