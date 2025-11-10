<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * TFoodOrderFixFixture
 */
class TFoodOrderFixFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public string $table = 't_food_order_fix';
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
                'user_id' => 'Lorem ipsum d',
                'order_date' => '2025-08-13',
                'deli_req_date' => '2025-08-13',
                'deli_shedule_date' => '2025-08-13',
                'food_id' => 'Lore',
                'order_quantity' => 1,
                'order_status' => '',
                'del_flg' => '',
                'create_user' => 'Lor',
                'create_date' => '2025-08-13 16:33:17',
                'update_user' => 'Lor',
                'update_date' => '2025-08-13 16:33:17',
            ],
        ];
        parent::init();
    }
}
