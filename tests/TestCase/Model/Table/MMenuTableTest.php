<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\MMenuTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\MMenuTable Test Case
 */
class MMenuTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\MMenuTable
     */
    protected $MMenu;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.MMenu',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('MMenu') ? [] : ['className' => MMenuTable::class];
        $this->MMenu = $this->getTableLocator()->get('MMenu', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->MMenu);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\MMenuTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
