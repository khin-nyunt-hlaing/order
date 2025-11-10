<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\MServiceTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\MServiceTable Test Case
 */
class MServiceTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\MServiceTable
     */
    protected $MService;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.MService',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('MService') ? [] : ['className' => MServiceTable::class];
        $this->MService = $this->getTableLocator()->get('MService', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->MService);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\MServiceTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
