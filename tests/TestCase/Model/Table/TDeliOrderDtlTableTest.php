<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\TDeliOrderDtlTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\TDeliOrderDtlTable Test Case
 */
class TDeliOrderDtlTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\TDeliOrderDtlTable
     */
    protected $TDeliOrderDtl;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.TDeliOrderDtl',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('TDeliOrderDtl') ? [] : ['className' => TDeliOrderDtlTable::class];
        $this->TDeliOrderDtl = $this->getTableLocator()->get('TDeliOrderDtl', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->TDeliOrderDtl);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\TDeliOrderDtlTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
