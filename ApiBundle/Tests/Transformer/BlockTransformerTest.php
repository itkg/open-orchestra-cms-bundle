<?php

namespace OpenOrchestra\ApiBundle\Tests\Transformer;

use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use OpenOrchestra\ModelInterface\Model\NodeInterface;
use Phake;
use OpenOrchestra\ApiBundle\Transformer\BlockTransformer;

/**
 * Class BlockTransformerTest
 */
class BlockTransformerTest extends AbstractBaseTestCase
{
    protected $facadeClass = 'OpenOrchestra\ApiBundle\Facade\BlockFacade';
    protected $blockParameterManager;
    protected $generateFormManager;
    protected $displayBlockManager;
    protected $displayIconManager;
    protected $currentSiteManager;
    protected $transformerManager;
    protected $blockTransformer;
    protected $eventDispatcher;
    protected $nodeRepository;
    protected $blockFacade;
    protected $blockClass;
    protected $translator;
    protected $router;
    protected $node;

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->eventDispatcher = Phake::mock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $this->blockClass = 'OpenOrchestra\ModelBundle\Document\Block';
        $this->displayBlockManager = Phake::mock('OpenOrchestra\DisplayBundle\DisplayBlock\DisplayBlockManager');
        $this->displayIconManager = Phake::mock('OpenOrchestra\Backoffice\DisplayIcon\DisplayManager');
        $this->node = Phake::mock('OpenOrchestra\ModelInterface\Model\NodeInterface');
        $this->blockFacade = Phake::mock('OpenOrchestra\ApiBundle\Facade\BlockFacade');
        $this->blockParameterManager = Phake::mock('OpenOrchestra\BackofficeBundle\StrategyManager\BlockParameterManager');
        $this->generateFormManager = Phake::mock('OpenOrchestra\BackofficeBundle\StrategyManager\GenerateFormManager');
        Phake::when($this->generateFormManager)->getDefaultConfiguration(Phake::anyParameters())->thenReturn(array());

        $this->nodeRepository = Phake::mock('OpenOrchestra\ModelInterface\Repository\NodeRepositoryInterface');

        $this->router = Phake::mock('Symfony\Component\Routing\RouterInterface');
        Phake::when($this->router)->generate(Phake::anyParameters())->thenReturn('route');
        $this->transformerManager = Phake::mock('OpenOrchestra\BaseApi\Transformer\TransformerManager');
        Phake::when($this->transformerManager)->getRouter()->thenReturn($this->router);

        $this->currentSiteManager = Phake::mock('OpenOrchestra\BaseBundle\Context\CurrentSiteIdInterface');
        Phake::when($this->currentSiteManager)->getCurrentSiteId()->thenReturn('1');
        Phake::when($this->currentSiteManager)->getCurrentSiteDefaultLanguage()->thenReturn('fr');

        $this->translator = Phake::mock('Symfony\Component\Translation\TranslatorInterface');

        $this->blockTransformer = new BlockTransformer(
            $this->facadeClass,
            $this->displayBlockManager,
            $this->displayIconManager,
            $this->blockClass,
            $this->blockParameterManager,
            $this->generateFormManager,
            $this->nodeRepository,
            $this->currentSiteManager,
            $this->translator,
            $this->eventDispatcher
        );
        $this->blockTransformer->setContext($this->transformerManager);
    }

    /**
     * Test getName
     */
    public function testGetName()
    {
        $name = $this->blockTransformer->getName();

        $this->assertSame('block', $name);
    }

    /**
     * Test transform
     *
     * @param string     $component
     * @param array      $attributes
     * @param string     $label
     * @param array|null $expectedAttributes
     *
     * @dataProvider blockTransformProvider
     */
    public function testTransform(
        $component,
        $attributes,
        $label,
        $expectedAttributes = null
    )
    {
        $html = 'ok';
        $block = Phake::mock('OpenOrchestra\ModelInterface\Model\BlockInterface');
        $response = Phake::mock('Symfony\Component\HttpFoundation\Response');
        $transformer = Phake::mock('OpenOrchestra\BaseApi\Transformer\TransformerInterface');
        $facade = Phake::mock('OpenOrchestra\ApiBundle\Facade\UiModelFacade');

        Phake::when($block)->getComponent()->thenReturn($component);
        Phake::when($block)->getLabel()->thenReturn($label);
        Phake::when($block)->getAttributes()->thenReturn($attributes);
        Phake::when($this->displayBlockManager)->show($block)->thenReturn($response);
        Phake::when($response)->getContent()->thenReturn($html);
        Phake::when($this->displayIconManager)->show($component)->thenReturn('icon');

        Phake::when($this->transformerManager)->get('ui_model')->thenReturn($transformer);
        Phake::when($transformer)->transform(Phake::anyParameters())->thenReturn($facade);

        $facadeResult = $this->blockTransformer->transform($block, true, 'root', 0, 0, 0, 'fakeId');

        $this->assertInstanceOf('OpenOrchestra\ApiBundle\Facade\BlockFacade', $facadeResult);
        $this->assertSame($component, $facadeResult->component);
        $this->assertInstanceOf('OpenOrchestra\ApiBundle\Facade\UiModelFacade', $facadeResult->uiModel);
        $this->assertArrayHasKey('_self_form', $facadeResult->getLinks());
        if (is_null($expectedAttributes)) {
            $expectedAttributes = $attributes;
        }
        $this->assertSame($expectedAttributes, $facadeResult->getAttributes());
        Phake::verify($this->router)->generate(Phake::anyParameters());

        if (!$label) {
            Phake::verify($this->translator)->trans('open_orchestra_backoffice.block.' . $component . '.title');
        }
    }

    /**
     * @return array
     */
    public function blockTransformProvider()
    {
        return array(
            array('sample', array('title' => 'title one', 'author' => 'me'), 'Sample'),
            array('sample', array('title' => 'news', 'author' => 'benj', 'text' => 'Hello world'), 'Sample'),
            array('news', array('title' => 'news', 'author' => 'benj', 'text' => 'Hello everybody'), 'News'),
            array('menu', array(), 'Menu'),
            array('menu', array('array' => array('test' => 'test')), 'Menu', array('array' => '{"test":"test"}')),
            array('menu', array(), null),
        );
    }

    /**
     * @param $areas
     * @param $nodeId
     * @param $nodeMongoId
     * @param $isInside
     * @param $isDeletable
     *
     * @dataProvider provideBlockDeletable
     */
    public function testBlockTransformerIsDeletable($areas, $nodeId, $nodeMongoId, $isInside, $isDeletable)
    {
        $html = 'ok';
        $component = 'fakeComponent';
        $label = 'fakeLabel';
        $block = Phake::mock('OpenOrchestra\ModelInterface\Model\BlockInterface');
        $response = Phake::mock('Symfony\Component\HttpFoundation\Response');
        $transformer = Phake::mock('OpenOrchestra\BaseApi\Transformer\TransformerInterface');
        $facade = Phake::mock('OpenOrchestra\ApiBundle\Facade\UiModelFacade');

        Phake::when($block)->getComponent()->thenReturn($component);
        Phake::when($block)->getLabel()->thenReturn($label);
        Phake::when($block)->getAttributes()->thenReturn(array());
        Phake::when($block)->getAreas()->thenReturn($areas);
        Phake::when($this->displayBlockManager)->show($block)->thenReturn($response);
        Phake::when($response)->getContent()->thenReturn($html);
        Phake::when($this->displayIconManager)->show($component)->thenReturn('icon');

        Phake::when($this->transformerManager)->get('ui_model')->thenReturn($transformer);
        Phake::when($transformer)->transform(Phake::anyParameters())->thenReturn($facade);

        $facadeResult = $this->blockTransformer->transform($block, $isInside, $nodeId, 0, 0, 0, $nodeMongoId);

        $this->assertSame($facadeResult->isDeletable, $isDeletable);
    }

    /**
     * @return array
     */
    public function provideBlockDeletable()
    {
        return array(
            array(array(), 'fakeNodeId', 'fakeMongoId', false, true),
            array(array(), NodeInterface::TRANSVERSE_NODE_ID, 0, true, true),
            array(array(array('nodeId' => 'fakeAreaId')), NodeInterface::TRANSVERSE_NODE_ID, 0, true, false),
            array(array(array('nodeId' => 0)), NodeInterface::TRANSVERSE_NODE_ID, 0, true, true),
        );
    }

    /**
     * @param string $nodeId
     * @param array  $result
     * @param string $facadeNodeId
     * @param int    $blockId
     * @param array  $blockParameter
     *
     * @dataProvider blockReverseTransformProvider
     */
    public function testReverseTransformToArray($nodeId, $result, $facadeNodeId, $blockId, array $blockParameter = array())
    {
        $this->blockFacade->nodeId = $facadeNodeId;
        $this->blockFacade->blockId = $blockId;

        $nodeTransverse = Phake::mock('OpenOrchestra\ModelInterface\Model\NodeInterface');
        Phake::when($this->node)->getNodeId()->thenReturn($nodeId);
        $block = Phake::mock('OpenOrchestra\ModelInterface\Model\BlockInterface');
        Phake::when($this->node)->getBlock(Phake::anyParameters())->thenReturn($block);
        Phake::when($nodeTransverse)->getBlock(Phake::anyParameters())->thenReturn($block);
        Phake::when($this->blockParameterManager)->getBlockParameter(Phake::anyParameters())->thenReturn($blockParameter);
        $siteId = $this->currentSiteManager->getCurrentSiteId();
        Phake::when($this->nodeRepository)->findInLastVersion($facadeNodeId, $this->node->getLanguage(), $siteId)->thenReturn($nodeTransverse);

        $expected = $this->blockTransformer->reverseTransformToArray($this->blockFacade, $this->node);

        $this->assertSame($result, $expected);
    }

    /**
     * @return array
     */
    public function blockReverseTransformProvider()
    {
        return array(
            array('fixture_full', array('blockParameter' => array(), 'blockId' => 5, 'nodeId' => 0), 'fixture_full', 5),
            array('fixture_full', array('blockParameter' => array(), 'blockId' => 0, 'nodeId' => 0), 'fixture_full', 0),
            array('fixture_about_us', array('blockParameter' => array(), 'blockId' => 3, 'nodeId' => 'fixture_full'), 'fixture_full', 3),
            array('fixture_about_us', array('blockParameter' => array('newsId'), 'blockId' => 3, 'nodeId' => 'fixture_full'), 'fixture_full', 3, array('newsId')),
        );
    }

    /**
     * @param array  $result
     * @param string $component
     * @param int    $blockId
     * @param array  $blockParameter
     *
     * @dataProvider blockReverseTransformProvider2
     */
    public function testReverseTransformToArrayComponent($result, $component, $blockId, array $blockParameter = array())
    {
        $this->blockFacade->component = $component;
        Phake::when($this->node)->getBlockIndex(Phake::anyParameters())->thenReturn($blockId);
        Phake::when($this->blockParameterManager)->getBlockParameter(Phake::anyParameters())->thenReturn($blockParameter);

        $expected = $this->blockTransformer->reverseTransformToArray($this->blockFacade, $this->node);

        $this->assertSame($result, $expected);
        Phake::verify($this->node)->addBlock(Phake::anyParameters());
        Phake::verify($this->generateFormManager)->getDefaultConfiguration(Phake::anyParameters());
        Phake::verify($this->eventDispatcher)->dispatch(Phake::anyParameters());
    }

    /**
     * @return array
     */
    public function blockReverseTransformProvider2()
    {
        return array(
            array(array('blockParameter' => array(), 'blockId' => 2, 'nodeId' => 0), 'sample', 2),
            array(array('blockParameter' => array(), 'blockId' => 3, 'nodeId' => 0), 'menu', 3),
            array(array('blockParameter' => array('newsId'), 'blockId' => 3, 'nodeId' => 0), 'news', 3, array('newsId')),
        );
    }

    /**
     * @param array  $result
     * @param string $facadeNodeId
     * @param int    $blockId
     *
     * @dataProvider blockReverseTransformProviderWithoutNode
     */
    public function testReverseTransformWithoutNode($result, $facadeNodeId, $blockId)
    {
        $this->blockFacade->nodeId = $facadeNodeId;
        $this->blockFacade->blockId = $blockId;

        $expected = $this->blockTransformer->reverseTransformToArray($this->blockFacade);

        $this->assertSame($result, $expected);
    }

    /**
     * @return array
     */
    public function blockReverseTransformProviderWithoutNode()
    {
        return array(
            array(array('blockParameter' => array(), 'blockId' => 0, 'nodeId' => 'fixture_full'), 'fixture_full', 0),
            array(array('blockParameter' => array(), 'blockId' => 3, 'nodeId' => 'fixture_full'), 'fixture_full', 3),
        );
    }

    /**
     * @param string $component
     * @param int    $blockIndex
     * @param array  $blockParameter
     *
     * @dataProvider provideComponentAndBlockIndex
     */
    public function testReverseTransformWithComponent($component, $blockIndex, array $blockParameter = array())
    {
        $this->blockFacade->component = $component;
        Phake::when($this->node)->getBlockIndex(Phake::anyParameters())->thenReturn($blockIndex);
        Phake::when($this->blockParameterManager)->getBlockParameter(Phake::anyParameters())->thenReturn($blockParameter);

        $result = $this->blockTransformer->reverseTransformToArray($this->blockFacade, $this->node);

        $this->assertSame(array('blockParameter' => $blockParameter, 'blockId' => $blockIndex, 'nodeId' => 0), $result);
        Phake::verify($this->node)->addBlock(Phake::anyParameters());
        Phake::verify($this->node)->getBlockIndex(Phake::anyParameters());
        Phake::verify($this->generateFormManager)->getDefaultConfiguration(Phake::anyParameters());
        Phake::verify($this->eventDispatcher)->dispatch(Phake::anyParameters());
    }

    /**
     * @return array
     */
    public function provideComponentAndBlockIndex()
    {
        return array(
            array('Sample', 1),
            array('TinyMCE', 2),
            array('Carrossel', 0),
            array('Carrossel', 0, array('page', 'width')),
            array('News', 1),
            array('News', 1, array('newsId')),
        );
    }

    /**
     * Test Exception transform with wrong object a parameters
     */
    public function testExceptionTransform()
    {
        $this->setExpectedException('OpenOrchestra\ApiBundle\Exceptions\TransformerParameterTypeException');
        $this->blockTransformer->transform(Phake::mock('stdClass'));
    }
}
