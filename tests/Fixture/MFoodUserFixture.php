<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * MFoodUserFixture
 */
class MFoodUserFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public string $table = 'm_food_user';
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'food_id' => '0f2fad29-ad43-46cd-b995-8a90c9fee076',
                'user_id' => '04294eb8-c600-40ea-bdd8-700d4bec148a',
                'create_user' => 'Lorem ipsum dolor sit amet',
                'create_date' => '2025-08-06 15:38:45',
                'update_user' => 'Lorem ipsum dolor sit amet',
                'update_date' => '2025-08-06 15:38:45',
            ],
        ];
        parent::init();
    }
}
