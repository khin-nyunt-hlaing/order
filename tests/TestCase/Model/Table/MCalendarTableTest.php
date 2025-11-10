<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\MCalendarTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\MCalendarTable Test Case
 */
class MCalendarTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\MCalendarTable
     */
    protected $MCalendar;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.MCalendar',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('MCalendar') ? [] : ['className' => MCalendarTable::class];
        $this->MCalendar = $this->getTableLocator()->get('MCalendar', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->MCalendar);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\MCalendarTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
