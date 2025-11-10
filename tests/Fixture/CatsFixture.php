<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * CatsFixture
 */
class CatsFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'name' => 'Lorem ipsum dolor sit amet',
                'breed' => 'Lorem ipsum dolor sit amet',
                'age' => 1,
                'created' => '2025-04-17 18:00:45',
                'modified' => '2025-04-17 18:00:45',
            ],
        ];
        parent::init();
    }
}
