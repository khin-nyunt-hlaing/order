<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * MfoodFixture
 */
class MfoodFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public string $table = 'M_FOOD';
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'FOOD_ID' => '1bf67d3a-96ab-4ede-ae25-a18c9fd14a8c',
                'FOOD_NAME' => 'Lorem ipsum dolor sit amet',
                'CATEGORY_ID' => 'Lore',
                'FOOD_SPECIFICATION' => 'Lorem ipsum dolor ',
                'TAX_INCLUDED_PRICE' => 1,
                'DISP_NO' => 1.5,
                'DEL_FLG' => '',
                'CREATE_USER' => 'Lor',
                'CREATE_DATE' => '2025-04-18 17:30:09',
                'UPDATE_USER' => 'Lor',
                'UPDATE_DATE' => '2025-04-18 17:30:09',
            ],
        ];
        parent::init();
    }
}
