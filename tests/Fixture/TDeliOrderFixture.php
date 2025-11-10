<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * TDeliOrderFixture
 */
class TDeliOrderFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public string $table = 't_deli_order';
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
                'create_user' => 'Lorem ipsum dolor sit amet',
                'create_date' => '2025-07-24 15:20:34',
                'update_user' => 'Lorem ipsum dolor sit amet',
                'update_date' => '2025-07-24 15:20:34',
            ],
        ];
        parent::init();
    }
}
