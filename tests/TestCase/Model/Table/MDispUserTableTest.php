<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\MDispUserTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\MDispUserTable Test Case
 */
class MDispUserTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\MDispUserTable
     */
    protected $MDispUser;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.MDispUser',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('MDispUser') ? [] : ['className' => MDispUserTable::class];
        $this->MDispUser = $this->getTableLocator()->get('MDispUser', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->MDispUser);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\MDispUserTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
