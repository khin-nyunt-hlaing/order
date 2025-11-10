<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * MUserFixture
 */
class MUserFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public string $table = 'm_user';
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'USER_ID' => '9001f02b-4617-4919-acb8-ef6d6362d0c4',
                'USER_NAME' => 'Lorem ipsum dolor sit amet',
                'PASSWORD' => 'Lorem ipsum dolor sit amet',
                'QUESTION' => 'Lorem ipsum dolor sit amet',
                'ANSWER' => 'Lorem ipsum dolor sit amet',
                'STATUS' => '',
                'USE_SERVICE' => '',
                'USE_PATTERN' => '',
                'READ_TIME' => 1.5,
                'DISP_NO' => 1.5,
                'DEL_FLG' => '',
                'CREATE_USER' => 'Lor',
                'CREATE_DATE' => '2025-05-30 14:41:06',
                'UPDATE_USER' => 'Lor',
                'UPDATE_DATE' => '2025-05-30 14:41:06',
                'address' => 'Lorem ipsum dolor sit amet',
            ],
        ];
        parent::init();
    }
}
