<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\MSystemSettingTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\MSystemSettingTable Test Case
 */
class MSystemSettingTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\MSystemSettingTable
     */
    protected $MSystemSetting;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.MSystemSetting',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('MSystemSetting') ? [] : ['className' => MSystemSettingTable::class];
        $this->MSystemSetting = $this->getTableLocator()->get('MSystemSetting', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->MSystemSetting);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\MSystemSettingTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
