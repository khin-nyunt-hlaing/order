<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\MKubunTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\MKubunTable Test Case
 */
class MKubunTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\MKubunTable
     */
    protected $MKubun;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.MKubun',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('MKubun') ? [] : ['className' => MKubunTable::class];
        $this->MKubun = $this->getTableLocator()->get('MKubun', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->MKubun);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\MKubunTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
