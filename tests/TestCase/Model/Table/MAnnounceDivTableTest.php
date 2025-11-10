<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\MAnnounceDivTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\MAnnounceDivTable Test Case
 */
class MAnnounceDivTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\MAnnounceDivTable
     */
    protected $MAnnounceDiv;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.MAnnounceDiv',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('MAnnounceDiv') ? [] : ['className' => MAnnounceDivTable::class];
        $this->MAnnounceDiv = $this->getTableLocator()->get('MAnnounceDiv', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->MAnnounceDiv);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\MAnnounceDivTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
