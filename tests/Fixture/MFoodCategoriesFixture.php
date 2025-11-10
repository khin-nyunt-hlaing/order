<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * MFoodCategoriesFixture
 */
class MFoodCategoriesFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public string $table = 'm_food_category';
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'category_id' => 1,
                'category_name' => 'Lorem ipsum dolor sit amet',
                'disp_no' => 1.5,
                'del_flg' => '',
                'create_user' => 'Lor',
                'create_date' => '2025-04-24 16:27:36',
                'update_user' => 'Lor',
                'update_date' => '2025-04-24 16:27:36',
            ],
        ];
        parent::init();
    }
}
