<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * MOrderFixture
 */
class MOrderFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public string $table = 'm_order';
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'deli_order_id' => 1,
                'user_id' => 'Lorem ipsum d',
                'term_id' => 1,
                'order_status' => '',
                'del_flg' => '',
                'create_user' => 'Lorem ipsum d',
                'create_date' => '2025-10-24 11:57:57',
                'update_user' => 'Lorem ipsum d',
                'update_date' => '2025-10-24 11:57:57',
            ],
        ];
        parent::init();
    }
}
