<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * FoodFixture
 */
class FoodFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public string $table = 'm_food';
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'food_id' => '379430ac-ef88-4086-8b7d-2b27d1e8fa3f',
                'food_name' => 'Lorem ipsum dolor sit amet',
                'category_id' => 1,
                'food_specification' => 'Lorem ipsum dolor ',
                'tax_included_price' => 1,
                'disp_no' => 1.5,
                'del_flg' => '',
                'create_user' => 'Lor',
                'create_date' => '2025-04-23 11:58:22',
                'update_user' => 'Lor',
                'update_date' => '2025-04-23 11:58:22',
            ],
        ];
        parent::init();
    }
}
