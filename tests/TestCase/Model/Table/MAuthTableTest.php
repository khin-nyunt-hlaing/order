<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\MAuthTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\MAuthTable Test Case
 */
class MAuthTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\MAuthTable
     */
    protected $MAuth;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.MAuth',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('MAuth') ? [] : ['className' => MAuthTable::class];
        $this->MAuth = $this->getTableLocator()->get('MAuth', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->MAuth);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\MAuthTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
