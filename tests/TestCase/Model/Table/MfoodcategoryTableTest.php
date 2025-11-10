<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\MfoodcategoryTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\MfoodcategoryTable Test Case
 */
class MfoodcategoryTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\MfoodcategoryTable
     */
    protected $Mfoodcategory;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Mfoodcategory',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Mfoodcategory') ? [] : ['className' => MfoodcategoryTable::class];
        $this->Mfoodcategory = $this->getTableLocator()->get('Mfoodcategory', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Mfoodcategory);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\MfoodcategoryTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
