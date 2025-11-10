<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * MServiceFixture
 */
class MServiceFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public string $table = 'm_service';
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'service_id' => 1,
                'service_name' => 'Lorem ipsum dolor sit amet',
                'disp_no' => 1.5,
                'del_flg' => '',
                'create_user' => 'Lor',
                'create_date' => '2025-05-02 15:53:04',
                'update_user' => 'Lor',
                'update_date' => '2025-05-02 15:53:04',
            ],
        ];
        parent::init();
    }
}
