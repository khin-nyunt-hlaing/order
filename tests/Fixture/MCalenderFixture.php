<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * McalendarFixture
 */
class McalendarFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public string $table = 'm_calendar';
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'calendar_date' => '2025-07-18',
                'holiday_flg' => '',
                'del_flg' => '',
                'create_user' => 'Lor',
                'create_date' => '2025-07-18 11:45:50',
                'update_user' => 'Lor',
                'update_date' => '2025-07-18 11:45:50',
            ],
        ];
        parent::init();
    }
}
