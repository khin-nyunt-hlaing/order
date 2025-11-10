<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * MAuthFixture
 */
class MAuthFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public string $table = 'm_auth';
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'user_id' => 'Lorem ipsum d',
                'menu_id' => 'Lor',
                'use_div' => '',
                'create_user' => 'Lor',
                'create_date' => '2025-07-10 13:06:44',
                'update_user' => 'Lor',
                'update_date' => '2025-07-10 13:06:44',
            ],
        ];
        parent::init();
    }
}
