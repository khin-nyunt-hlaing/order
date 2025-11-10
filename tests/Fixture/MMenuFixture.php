<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * MMenuFixture
 */
class MMenuFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public string $table = 'm_menu';
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'menu_id' => 'f042d544-6e03-4beb-b031-21023e4931c2',
                'menu_name' => 'Lorem ipsum dolor sit amet',
                'controller' => 'Lorem ipsum dolor sit amet',
                'action' => 'Lorem ipsum dolor sit amet',
                'disp_no' => 1.5,
                'del_flg' => '',
                'create_user' => 'Lorem ipsum dolor sit amet',
                'create_date' => '2025-08-08 12:57:57',
                'update_user' => 'Lorem ipsum dolor sit amet',
                'update_date' => '2025-08-08 12:57:57',
            ],
        ];
        parent::init();
    }
}
