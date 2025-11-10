<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * MDeliveryPatternFixture
 */
class MDeliveryPatternFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public string $table = 'm_delivery_pattern';
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'use_pattern_id' => 1,
                'delivery_pattern_name' => 'Lorem ipsum dolor sit amet',
                'disp_no' => 1.5,
                'del_flg' => '',
                'create_user' => 'Lor',
                'create_date' => '2025-07-11 15:01:27',
                'update_user' => 'Lor',
                'update_date' => '2025-07-11 15:01:27',
            ],
        ];
        parent::init();
    }
}
