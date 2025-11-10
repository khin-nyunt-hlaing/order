<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * MDeliveryPatternSetFixture
 */
class MDeliveryPatternSetFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public string $table = 'm_delivery_pattern_set';
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
                'delivery_id' => 1,
                'disp_no' => 1.5,
                'del_flg' => '',
                'create_user' => 'Lorem ipsum dolor sit amet',
                'create_date' => '2025-07-11 15:17:55',
                'update_user' => 'Lorem ipsum dolor sit amet',
                'update_date' => '2025-07-11 15:17:55',
            ],
        ];
        parent::init();
    }
}
