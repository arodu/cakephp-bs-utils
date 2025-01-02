<?php
declare(strict_types=1);

namespace BsUtils\Test\TestCase\View\Helper;

use BsUtils\View\Helper\BsHelper;
use Cake\TestSuite\TestCase;
use Cake\View\View;

/**
 * BsUtils\View\Helper\BsHelper Test Case
 */
class BsHelperTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \BsUtils\View\Helper\BsHelper
     */
    protected $Bs;

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $view = new View();
        $this->Bs = new BsHelper($view);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Bs);

        parent::tearDown();
    }
}
