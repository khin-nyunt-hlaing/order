<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * IngredientsFixture
 */
class IngredientsFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'is_selected' => 1,
                'code_number' => 'Lorem ipsum dolor ',
                'product_name' => 'Lorem ipsum dolor sit amet',
                'category_name' => 'Lorem ipsum dolor sit amet',
                'specification' => 'Lorem ipsum dolor sit amet',
                'price_with_tax' => 1,
                'display_order' => 1,
            ],
        ];
        parent::init();
    }
}
