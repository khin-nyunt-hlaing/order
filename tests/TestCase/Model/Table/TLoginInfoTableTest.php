<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\TLoginInfoTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\TLoginInfoTable Test Case
 */
class TLoginInfoTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\TLoginInfoTable
     */
    protected $TLoginInfo;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.TLoginInfo',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('TLoginInfo') ? [] : ['className' => TLoginInfoTable::class];
        $this->TLoginInfo = $this->getTableLocator()->get('TLoginInfo', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->TLoginInfo);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\TLoginInfoTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
