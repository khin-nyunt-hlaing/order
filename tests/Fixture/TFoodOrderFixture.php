<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * TFoodOrderFixture
 */
class TFoodOrderFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public string $table = 't_food_order';
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'food_order_id' => 1,
                'user_id' => 'Lor',
                'order_date' => '2025-07-16',
                'deli_req_date' => '2025-07-16',
                'food_id' => 1,
                'order_quantity' => 1,
                'order_status' => '',
                'del_flg' => '',
                'create_user' => 'Lorem ipsum dolor sit amet',
                'create_date' => '2025-07-16 10:44:44',
                'update_user' => 'Lorem ipsum dolor sit amet',
                'update_date' => '2025-07-16 10:44:44',
            ],
        ];
        parent::init();
    }
}
