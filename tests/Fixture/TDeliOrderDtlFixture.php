<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * TDeliOrderDtlFixture
 */
class TDeliOrderDtlFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public string $table = 't_deli_order_dtl';
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
                'deli_order_dtl_id' => 1,
                'term_date' => '2025-07-29',
                'delivery_id' => 1,
                'quantity' => 1,
                'del_flg' => '',
                'create_user' => 'Lorem ipsum dolor sit amet',
                'create_date' => '2025-07-29 16:49:07',
                'update_user' => 'Lorem ipsum dolor sit amet',
                'update_date' => '2025-07-29 16:49:07',
            ],
        ];
        parent::init();
    }
}
