<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * MfoodcategoryFixture
 */
class MfoodcategoryFixture extends TestFixture
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
                'category_id' => '0a2625ca-8197-4eba-919f-19e58386a39f',
                'category_name' => 'Lorem ipsum dolor sit amet',
                'disp_no' => 1.5,
                'del_flg' => '',
                'create_user' => 'Lor',
                'create_date' => '2025-04-22 11:33:41',
                'update_user' => 'Lor',
                'update_date' => '2025-04-22 11:33:41',
            ],
        ];
        parent::init();
    }
}
