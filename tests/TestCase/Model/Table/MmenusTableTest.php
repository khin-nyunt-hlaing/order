<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\MmenusTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\MmenusTable Test Case
 */
class MmenusTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\MmenusTable
     */
    protected $Mmenus;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Mmenus',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Mmenus') ? [] : ['className' => MmenusTable::class];
        $this->Mmenus = $this->getTableLocator()->get('Mmenus', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Mmenus);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\MmenusTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
