<?php

namespace OpenOrchestra\ApiBundle\Tests\Transformer;

use Phake;
use OpenOrchestra\ApiBundle\Transformer\ContentTransformer;
use OpenOrchestra\ModelInterface\Model\ContentInterface;
use OpenOrchestra\BaseApi\Facade\FacadeInterface;
/**
 * Class ContentTransformerTest
 */
class ContentTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContentTransformer
     */
    protected $contentTransformer;

    protected $transformerAttribute;
    protected $transformerManager;
    protected $statusRepository;
    protected $eventDispatcher;
    protected $transformer;
    protected $statusId;
    protected $content;
    protected $router;
    protected $status;

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->content = Phake::mock('OpenOrchestra\ModelInterface\Model\ContentInterface');
        $this->status = Phake::mock('OpenOrchestra\ModelInterface\Model\StatusInterface');
        $this->statusId = 'StatusId';
        Phake::when($this->status)->getId(Phake::anyParameters())->thenReturn($this->statusId);

        $this->eventDispatcher = Phake::mock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->statusRepository = Phake::mock('OpenOrchestra\ModelInterface\Repository\StatusRepositoryInterface');
        Phake::when($this->statusRepository)->find(Phake::anyParameters())->thenReturn($this->status);

        $this->transformerAttribute = Phake::mock('OpenOrchestra\ApiBundle\Transformer\ContentAttributeTransformer');
        $this->transformer = Phake::mock('OpenOrchestra\ApiBundle\Transformer\StatusTransformer');
        $this->router = Phake::mock('Symfony\Component\Routing\RouterInterface');
        Phake::when($this->router)->generate(Phake::anyParameters())->thenReturn('route');

        $this->transformerManager = Phake::mock('OpenOrchestra\BaseApi\Transformer\TransformerManager');
        Phake::when($this->transformerManager)->get('status')->thenReturn($this->transformer);
        Phake::when($this->transformerManager)->get('content_attribute')->thenReturn($this->transformerAttribute);
        Phake::when($this->transformerManager)->getRouter()->thenReturn($this->router);

        $this->contentTransformer = new ContentTransformer($this->statusRepository, $this->eventDispatcher);
        $this->contentTransformer->setContext($this->transformerManager);
    }

    /**
     * test transform
     */
    public function testTransform()
    {
        $facade = Phake::mock('OpenOrchestra\BaseApi\Facade\FacadeInterface');
        $facade->label = 'draft';
        $facade->name = 'fakeName';
        $facade->value = 'fakeValue';

        $attribute = Phake::mock('OpenOrchestra\ModelInterface\Model\ContentAttributeInterface');
        Phake::when($this->content)->getAttributes()->thenReturn(array($attribute, $attribute));

        Phake::when($this->transformer)->transform(Phake::anyParameters())->thenReturn($facade);
        Phake::when($this->transformerAttribute)->transform(Phake::anyParameters())->thenReturn($facade);

        $facade = $this->contentTransformer->transform($this->content);

        Phake::verify($this->content)->getAttributes();
        Phake::verify($this->content,Phake::atLeast(1))->getContentType();
        Phake::verify($this->content,Phake::atLeast(1))->getName();
        Phake::verify($this->content,Phake::atLeast(1))->getContentTypeVersion();
        Phake::verify($this->content,Phake::atLeast(1))->getStatus();
        Phake::verify($this->content,Phake::atLeast(1))->getCreatedAt();
        Phake::verify($this->content,Phake::atLeast(1))->getUpdatedAt();
        Phake::verify($this->content,Phake::atLeast(1))->isDeleted();
        Phake::verify($this->content,Phake::atLeast(1))->isLinkedToSite();
        Phake::verify($this->content,Phake::atLeast(1))->getLanguage();
        Phake::verify($this->content,Phake::atLeast(1))->getContentId();
        Phake::verify($this->content,Phake::atLeast(1))->getVersion();

        $this->assertInstanceOf('OpenOrchestra\ApiBundle\Facade\ContentFacade', $facade);
        $this->assertArrayHasKey('_self_form', $facade->getLinks());
        $this->assertArrayHasKey('_self_duplicate', $facade->getLinks());
        $this->assertArrayHasKey('_self_version', $facade->getLinks());
        $this->assertArrayHasKey('_language_list', $facade->getLinks());
        $this->assertArrayHasKey('_self', $facade->getLinks());
        $this->assertArrayHasKey('_self_without_parameters', $facade->getLinks());
        $this->assertArrayHasKey('_self_delete', $facade->getLinks());
        $this->assertArrayHasKey('_status_list', $facade->getLinks());
        $this->assertArrayHasKey('_self_status_change', $facade->getLinks());
    }

    /**
     * test reverseTransform
     *
     * @param FacadeInterface  $facade
     * @param ContentInterface $source
     * @param int              $searchCount
     * @param int              $setCount
     *
     * @dataProvider changeStatusProvider
     */
    public function testReverseTransform($facade, $source, $searchCount, $setCount)
    {
        $this->contentTransformer->reverseTransform($facade, $source);

        Phake::verify($this->statusRepository, Phake::times($searchCount))->find(Phake::anyParameters());
        Phake::verify($this->eventDispatcher, Phake::times($setCount))->dispatch(Phake::anyParameters());
    }

    /**
     * @return array
     */
    public function changeStatusProvider()
    {
        $content = Phake::mock('OpenOrchestra\ModelInterface\Model\ContentInterface');

        $fromStatus = Phake::mock('OpenOrchestra\ModelInterface\Model\StatusInterface');
        Phake::when($fromStatus)->getId()->thenReturn('fromStatus');
        Phake::when($content)->getStatus()->thenReturn($fromStatus);

        $facade1 = Phake::mock('OpenOrchestra\ApiBundle\Facade\ContentFacade');

        $facade2 = Phake::mock('OpenOrchestra\ApiBundle\Facade\ContentFacade');
        $facade2->statusId = 'statusId';

        return array(
            array($facade1, null, 0, 0),
            array($facade1, $content, 0, 0),
            array($facade2, $content, 1, 1)
        );
    }

    /**
     * Test getName
     */
    public function testGetName()
    {
        $this->assertSame('content', $this->contentTransformer->getName());
    }
}
