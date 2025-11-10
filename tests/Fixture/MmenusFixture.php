<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * MmenusFixture
 */
class MmenusFixture extends TestFixture
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
                'MENU_ID' => 1,
                'MENU_NAME' => 'Lorem ipsum dolor sit amet',
                'DISP_NO' => 1.5,
                'DEL_FLG' => 'L',
                'CREATE_USER' => 'Lor',
                'CREATE_DATE' => '2025-05-01 13:21:37',
                'UPDATE_USER' => 'Lor',
                'UPDATE_DATE' => '2025-05-01 13:21:37',
            ],
        ];
        parent::init();
    }
}
