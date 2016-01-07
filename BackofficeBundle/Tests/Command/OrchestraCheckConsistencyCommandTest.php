<?php

namespace OpenOrchestra\BackofficeBundle\Tests\Command;

use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use Phake;
use OpenOrchestra\BackofficeBundle\Command\OrchestraCheckConsistencyCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;

/**
 * Class OrchestraCheckConsistencyCommandTest
 */
class OrchestraCheckConsistencyCommandTest extends AbstractBaseTestCase
{
    protected $trans;
    protected $kernel;
    protected $container;
    protected $nodeManager;
    protected $nodeRepository;

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->nodeManager = Phake::mock('OpenOrchestra\BackofficeBundle\Manager\NodeManager');

        $this->trans = Phake::mock('Symfony\Component\Translation\TranslatorInterface');
        Phake::when($this->trans)->trans('open_orchestra_backoffice.command.node.success')->thenReturn('success');
        Phake::when($this->trans)->trans('open_orchestra_backoffice.command.node.error')->thenReturn('error');
        Phake::when($this->trans)->trans('open_orchestra_backoffice.command.empty_choices')->thenReturn('empty');

        $this->nodeRepository = Phake::mock('OpenOrchestra\ModelInterface\Repository\NodeRepositoryInterface');

        $this->container = $this->container = Phake::mock('Symfony\Component\DependencyInjection\Container');
        Phake::when($this->container)->get('open_orchestra_model.repository.node')->thenReturn($this->nodeRepository);
        Phake::when($this->container)->get('translator')->thenReturn($this->trans);
        Phake::when($this->container)->get('open_orchestra_backoffice.manager.node')->thenReturn($this->nodeManager);

        $this->kernel = Phake::mock('Symfony\Component\HttpKernel\Kernel');
        Phake::when($this->kernel)->getContainer()->thenReturn($this->container);
    }

    /**
     * Test command OrchestraCheckConsistency
     */
    public function testExecute()
    {
        $application = new Application($this->kernel);
        $application->add(new OrchestraCheckConsistencyCommand());

        $command = $application->find('orchestra:check');
        $commandTest = new CommandTester($command);
        $commandTest->execute(array('command' => $command->getName()));

        $this->assertSame("empty\n", $commandTest->getDisplay());
    }

    /**
     * Test command OrchestraCheckConsistency with node option
     */
    public function testExecuteNode()
    {
        Phake::when($this->nodeManager)->nodeConsistency(Phake::anyParameters())->thenReturn(true);

        $application = new Application($this->kernel);
        $application->add(new OrchestraCheckConsistencyCommand());

        $command = $application->find('orchestra:check');
        $commandTest = new CommandTester($command);
        $commandTest->execute(array('command' => $command->getName(), '--nodes' => true));

        $this->assertSame("success\n", $commandTest->getDisplay());
    }

    /**
     * Test command OrchestraCheckConsistency with node option and false return
     */
    public function testExecuteNodeError()
    {
        Phake::when($this->nodeManager)->nodeConsistency(Phake::anyParameters())->thenReturn(false);

        $application = new Application($this->kernel);
        $application->add(new OrchestraCheckConsistencyCommand());

        $command = $application->find('orchestra:check');
        $commandTest = new CommandTester($command);
        $commandTest->execute(array('command' => $command->getName(), '--nodes' => true));

        $this->assertSame("error\n", $commandTest->getDisplay());
    }
}
