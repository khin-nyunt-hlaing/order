<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\MFoodUserTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\MFoodUserTable Test Case
 */
class MFoodUserTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\MFoodUserTable
     */
    protected $MFoodUser;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.MFoodUser',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('MFoodUser') ? [] : ['className' => MFoodUserTable::class];
        $this->MFoodUser = $this->getTableLocator()->get('MFoodUser', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->MFoodUser);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\MFoodUserTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
