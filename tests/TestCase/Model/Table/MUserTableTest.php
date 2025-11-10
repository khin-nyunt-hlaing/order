<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\MUserTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\MUserTable Test Case
 */
class MUserTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\MUserTable
     */
    protected $MUser;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.MUser',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('MUser') ? [] : ['className' => MUserTable::class];
        $this->MUser = $this->getTableLocator()->get('MUser', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->MUser);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\MUserTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
