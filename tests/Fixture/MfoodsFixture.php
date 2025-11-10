<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * MfoodsFixture
 */
class MfoodsFixture extends TestFixture
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
                'food_id' => '2cb4ebec-3ea9-47bf-be53-3445b0dc770d',
                'food_name' => 'Lorem ipsum dolor sit amet',
                'category_id' => 'Lore',
                'food_specification' => 'Lorem ipsum dolor ',
                'tax_included_price' => 1,
                'disp_no' => 1.5,
                'del_flg' => '',
                'create_user' => 'Lor',
                'create_date' => '2025-04-22 10:27:08',
                'update_user' => 'Lor',
                'update_date' => '2025-04-22 10:27:08',
            ],
        ];
        parent::init();
    }
}
