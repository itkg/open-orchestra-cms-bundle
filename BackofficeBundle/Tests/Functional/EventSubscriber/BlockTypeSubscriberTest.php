<?php

namespace OpenOrchestra\BackofficeBundle\Tests\Functional\EventSubscriber;

use OpenOrchestra\BackofficeBundle\Tests\Functional\AbstractAuthentificatedTest;
use OpenOrchestra\DisplayBundle\DisplayBlock\Strategies\ConfigurableContentStrategy;
use OpenOrchestra\DisplayBundle\DisplayBlock\Strategies\ContentListStrategy;
use OpenOrchestra\DisplayBundle\DisplayBlock\Strategies\VideoStrategy;
use OpenOrchestra\Media\DisplayBlock\Strategies\GalleryStrategy;
use OpenOrchestra\ModelBundle\Document\Block;
use OpenOrchestra\ModelInterface\Model\BlockInterface;
use Symfony\Component\Form\FormFactoryInterface;
use OpenOrchestra\ModelInterface\Repository\ReadContentRepositoryInterface;
use OpenOrchestra\ModelInterface\Repository\ContentRepositoryInterface;

/**
 * Class BlockTypeSubscriberTest
 *
 * @group backofficeTest
 */
class BlockTypeSubscriberTest extends AbstractAuthentificatedTest
{
    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;
    protected $keywords;
    protected $keywordRepository;

    /**
     * Set up the test
     */
    public function setUp()
    {
        parent::setUp();
        $this->keywordRepository = static::$kernel->getContainer()->get('open_orchestra_model.repository.keyword');
        $this->formFactory = static::$kernel->getContainer()->get('form.factory');
    }

    /**
     * Test video block : checkbox unique to uncheck
     */
    public function testVideoBlock()
    {
        $block = new Block();
        $block->setComponent(VideoStrategy::NAME);
        $block->addAttribute('videoType', 'youtube');
        $block->addAttribute('youtubeFs', true);

        $form = $this->formFactory->create('oo_block', $block, array('csrf_protection' => false));

        $form->submit(array(
            'id' => 'testId',
            'class' => 'testClass',
            'videoType' => 'youtube',
            'youtubeVideoId' => 'videoId',
            'youtubeAutoplay' => '1',
        ));

        $this->assertTrue($form->isSynchronized());
        /** @var BlockInterface $data */
        $data = $form->getConfig()->getData();
        $this->assertBlock($data);
        $this->assertSame('videoId', $data->getAttribute('youtubeVideoId'));
        $this->assertTrue($data->getAttribute('youtubeAutoplay'));
        $this->assertNull($data->getAttribute('youtubeFs'));
    }

    /**
     * @param string $component
     * @param array  $value
     *
     * @dataProvider provideComponentAndData
     */
    public function testMultipleBlock($component, $value)
    {
        $block = new Block();
        $block->setComponent($component);

        $form = $this->formFactory->create('oo_block', $block, array('csrf_protection' => false));

        $submittedValue = array_merge(array('id' => 'testId', 'class' => 'testClass'), $value);
        $form->submit($submittedValue);

        $this->assertTrue($form->isSynchronized());
        /** @var BlockInterface $data */
        $data = $form->getConfig()->getData();
        $this->assertBlock($data);
        foreach ($value as $key => $sendData) {
            $this->assertSame($sendData, $data->getAttribute($key));
        }
    }

    /**
     * @return array
     */
    public function provideComponentAndData()
    {
        return array(
            array(GalleryStrategy::NAME, array(
                'pictures' => array(
                    'media1',
                    'media2'
                )
            )),
            array(ContentListStrategy::NAME, array(
                'contentNodeId' => 'news',
                'contentTemplateEnabled' => true,
            )),
            array(ConfigurableContentStrategy::NAME, array(
                'contentSearch' => array(
                    'contentType' => 'car',
                    'keywords' => null,
                    'choiceType' => ReadContentRepositoryInterface::CHOICE_AND,
                ),
                'contentTemplateEnabled' => true,
            ))
        );
    }

    /**
     * @param string $component
     * @param array  $value
     *
     * @dataProvider provideComponentAndDataAndTransformedValue
     */
    public function testMultipleBlockWithDataTransformation($component, $value)
    {
        $block = new Block();
        $block->setComponent($component);
        $form = $this->formFactory->create('oo_block', $block, array('csrf_protection' => false));
        $submittedValue = array_merge(array('id' => 'testId', 'class' => 'testClass'), $value);
        $value['contentSearch']['keywords'] = $this->replaceKeywordLabelById($value['contentSearch']['keywords']);
        $form->submit($submittedValue);

        $this->assertTrue($form->isSynchronized());
        /** @var BlockInterface $data */
        $data = $form->getConfig()->getData();
        $this->assertBlock($data);
        foreach ($value as $key => $receivedData) {
            $this->assertSame($receivedData, $data->getAttribute($key));
        }
    }

    /**
     * @return array
     */
    public function provideComponentAndDataAndTransformedValue()
    {
        return array(
                array(ContentListStrategy::NAME, array(
                        'contentNodeId' => 'news',
                        'contentTemplateEnabled' => true,
                        'contentSearch' => array(
                                'keywords' => 'lorem AND ipsum',
                            )
                )),
        );
    }

    /**
     * @param $data
     */
    protected function assertBlock($data)
    {
        $this->assertInstanceOf('OpenOrchestra\ModelInterface\Model\BlockInterface', $data);
        $this->assertSame('testId', $data->getId());
        $this->assertSame('testClass', $data->getClass());
    }

    /**
     * @param string $condition
     *
     * @return array
     */
    protected function replaceKeywordLabelById($condition)
    {
        $conditionWithoutOperator = preg_replace(ContentRepositoryInterface::OPERATOR_SPLIT, ' ', $condition);
        $conditionArray = explode(' ', $conditionWithoutOperator);

        foreach ($conditionArray as $keyword) {
            if ($keyword != '') {
                $keywordDocument = $this->keywordRepository->findOneByLabel($keyword);
                if (!is_null($keywordDocument)) {
                    $condition = str_replace($keyword, $keywordDocument->getId(), $condition);
                } else {
                    return '';
                }
            }
        }

        return $condition;
    }
}
