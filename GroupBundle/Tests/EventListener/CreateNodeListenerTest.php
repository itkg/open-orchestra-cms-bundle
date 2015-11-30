<?php

namespace OpenOrchestra\GroupBundle\Tests\EventListener;

use OpenOrchestra\GroupBundle\EventListener\CreateNodeListener;
use Phake;

/**
 * Class CreateNodeListenerTest
 */
class CreateNodeListenerTest extends AbstractNodeGroupRoleListenerTest
{
    /**
     * @var CreateNodeListener
     */
    protected $listener;
    protected $groupRepository;
    protected $documentManager;

    /**
     * setUp
     */
    public function setUp()
    {
        parent::setUp();
        $this->documentManager = Phake::mock('Doctrine\ODM\MongoDB\DocumentManager');
        $this->groupRepository = Phake::mock('OpenOrchestra\BackofficeBundle\Repository\GroupRepositoryInterface');
        Phake::when($this->container)->get('open_orchestra_user.repository.group')->thenReturn($this->groupRepository);
        Phake::when($this->lifecycleEventArgs)->getDocumentManager()->thenReturn($this->documentManager);

        $this->listener = new CreateNodeListener($this->nodeGroupRoleClass);
        $this->listener->setContainer($this->container);
    }

    /**
     * test if the method is callable
     */
    public function testMethodPrePersistCallable()
    {
        $this->assertTrue(method_exists($this->listener, 'postPersist'));
    }

    /**
     * @param array               $groups
     * @param int                 $countNodeGroupRole
     *
     * @dataProvider provideGroup
     */
    public function testPostPersist(array $groups, $countNodeGroupRole)
    {
        $node = Phake::mock('OpenOrchestra\ModelInterface\Model\NodeInterface');
        $countNodeGroupRole = count($this->nodesRoles) * $countNodeGroupRole;
        Phake::when($this->lifecycleEventArgs)->getDocument()->thenReturn($node);
        Phake::when($this->groupRepository)->findAllWithSite()->thenReturn($groups);

        $this->listener->postPersist($this->lifecycleEventArgs);

        Phake::verify($this->documentManager, Phake::times($countNodeGroupRole))->persist(Phake::anyParameters());
    }

    /**
     * @return array
     */
    public function provideGroup()
    {
        $group1 = Phake::mock('OpenOrchestra\BackofficeBundle\Model\GroupInterface');
        $group2 = Phake::mock('OpenOrchestra\BackofficeBundle\Model\GroupInterface');
        $site = Phake::mock('OpenOrchestra\ModelInterface\Model\SiteInterface');
        Phake::when($group1)->getSite()->thenReturn($site);
        Phake::when($group2)->getSite()->thenReturn($site);

        return array(
           array(array($group1, $group2), 2),
           array(array($group1), 1),
           array(array(), 0),
        );
    }
}
