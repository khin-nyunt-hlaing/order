<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\MOrderTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\MOrderTable Test Case
 */
class MOrderTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\MOrderTable
     */
    protected $MOrder;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.MOrder',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('MOrder') ? [] : ['className' => MOrderTable::class];
        $this->MOrder = $this->getTableLocator()->get('MOrder', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->MOrder);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\MOrderTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
