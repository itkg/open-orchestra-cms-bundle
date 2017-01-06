<?php

namespace OpenOrchestra\BackofficeBundle\DependencyInjection;

use OpenOrchestra\BackofficeBundle\DependencyInjection\OpenOrchestraBackofficeExtension;
use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use OpenOrchestra\DisplayBundle\DisplayBlock\Strategies\AudienceAnalysisStrategy;
use OpenOrchestra\DisplayBundle\DisplayBlock\Strategies\ConfigurableContentStrategy;
use OpenOrchestra\DisplayBundle\DisplayBlock\Strategies\ContentListStrategy;
use OpenOrchestra\DisplayBundle\DisplayBlock\Strategies\ContentStrategy;
use OpenOrchestra\DisplayBundle\DisplayBlock\Strategies\FooterStrategy;
use OpenOrchestra\DisplayBundle\DisplayBlock\Strategies\LanguageListStrategy;
use OpenOrchestra\DisplayBundle\DisplayBlock\Strategies\MenuStrategy;
use OpenOrchestra\DisplayBundle\DisplayBlock\Strategies\SubMenuStrategy;
use OpenOrchestra\DisplayBundle\DisplayBlock\Strategies\TinyMCEWysiwygStrategy;
use OpenOrchestra\DisplayBundle\DisplayBlock\Strategies\VideoStrategy;
use OpenOrchestra\DisplayBundle\DisplayBlock\Strategies\ContactStrategy;

/**
 * Class OpenOrchestraBackofficeExtensionTest
 */
class OpenOrchestraBackofficeExtensionTest extends AbstractBaseTestCase
{
    protected $defaultBlocks = array(
        FooterStrategy::NAME,
        LanguageListStrategy::NAME,
        MenuStrategy::NAME,
        SubMenuStrategy::NAME,
        ContentListStrategy::NAME,
        ContentStrategy::NAME,
        ConfigurableContentStrategy::NAME,
        TinyMCEWysiwygStrategy::NAME,
        VideoStrategy::NAME,
        AudienceAnalysisStrategy::NAME,
        ContactStrategy::NAME,
    );

    /**
     * Test default config
     */
    public function testDefaultConfig()
    {
        $container = $this->loadContainerFromFile('empty');

        $defaultLanguage = array(
            'en'=>'open_orchestra_backoffice.language.en',
            'fr'=>'open_orchestra_backoffice.language.fr',
            'de' => 'open_orchestra_backoffice.language.de',
        );
        $this->assertEquals($defaultLanguage, $container->getParameter('open_orchestra_backoffice.orchestra_choice.front_language'));
        $this->assertEquals('OpenOrchestraBackofficeBundle::layout.html.twig', $container->getParameter('open_orchestra_user.base_layout'));
        $this->assertEquals('OpenOrchestraBackofficeBundle::form.html.twig', $container->getParameter('open_orchestra_user.form_template'));

        $fixedAttributes = array(
            'maxAge',
            'label',
        );
        $this->assertEquals($fixedAttributes, $container->getParameter('open_orchestra_backoffice.block.fixed_attributes'));

        $this->assertEquals($this->defaultBlocks, $container->getParameter('open_orchestra.blocks'));

        $choice_frequence = array(
            "always"  => "open_orchestra_backoffice.form.changefreq.always",
            "hourly"  => "open_orchestra_backoffice.form.changefreq.hourly",
            "daily"   => "open_orchestra_backoffice.form.changefreq.daily",
            "weekly"  => "open_orchestra_backoffice.form.changefreq.weekly",
            "monthly" => "open_orchestra_backoffice.form.changefreq.monthly",
            "yearly"  => "open_orchestra_backoffice.form.changefreq.yearly",
            "never"   => "open_orchestra_backoffice.form.changefreq.never",
        );
        $this->assertEquals($choice_frequence, $container->getParameter('open_orchestra_backoffice.choice.frequence'));

        $this->assertEmpty($container->getDefinition('open_orchestra_backoffice.collector.front_role')->getMethodCalls());


        $configurationRoles = array (
            'firstpackage' =>
            array (
                'page' =>
                array (
                    'EDITORIAL_NODE_CONTRIBUTOR' => 'open_orchestra_backoffice.role.contributor',
                    'EDITORIAL_NODE_SUPER_EDITOR' => 'open_orchestra_backoffice.role.editor',
                    'EDITORIAL_NODE_SUPER_SUPRESSOR' => 'open_orchestra_backoffice.role.suppresor',
                ),
                'content' =>
                array (
                    'EDITORIAL_CONTENT_CONTRIBUTOR' => 'open_orchestra_backoffice.role.contributor',
                    'EDITORIAL_CONTENT_SUPER_EDITOR' => 'open_orchestra_backoffice.role.editor',
                    'EDITORIAL_CONTENT_SUPER_SUPRESSOR' => 'open_orchestra_backoffice.role.suppresor',
                ),
            ),
            'secondpackage' =>
            array (
                'trash' =>
                array (
                    'EDITORIAL_TRASH_RESTORER' => 'open_orchestra_backoffice.role.restorer',
                    'EDITORIAL_TRASH_SUPRESSOR' => 'open_orchestra_backoffice.role.contributor',
                ),
            ),
            'thirdpackage' =>
            array (
                'configuration' =>
                array (
                    'ROLE_SITE_ADMIN' => 'open_orchestra_backoffice.role.administrator',
                ),
            ),
        );

        $this->assertEquals($configurationRoles, $container->getParameter('open_orchestra_backoffice.configuration.roles'));
   }

    /**
     * Test with configuration
     */
    public function testConfigWithValue()
    {
        $container = $this->loadContainerFromFile('value');

        $defaultLanguage = array('fake_language' => 'fake_translation_language',);
        $this->assertEquals($defaultLanguage, $container->getParameter('open_orchestra_backoffice.orchestra_choice.front_language'));

        $blocks = array_merge(array("fakeBlocks"), $this->defaultBlocks);
        $this->assertEquals($blocks, $container->getParameter('open_orchestra.blocks'));

        $fixedAttributes = array(
            "fake_attribute",
        );
        $this->assertEquals($fixedAttributes, $container->getParameter('open_orchestra_backoffice.block.fixed_attributes'));

        $fields = $container->getParameter('open_orchestra_backoffice.field_types');
        $this->assertArrayHasKey('fake_field', $fields);
        $this->assertCount(11, $fields);

        $options = $container->getParameter('open_orchestra_backoffice.options');
        $this->assertArrayHasKey('fake_option', $options);
        $this->assertCount(15, $options);

        $this->assertSame(array(
            array('addRole', array('role_foo')),
            array('addRole', array('role_bar')),
        ), $container->getDefinition('open_orchestra_backoffice.collector.front_role')->getMethodCalls());
    }

    /**
     * @param string $file
     *
     * @return ContainerBuilder
     */
    private function loadContainerFromFile($file)
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', false);
        $container->setParameter('kernel.cache_dir', '/tmp');
        $container->setParameter('kernel.environment', 'prod');
        $container->registerExtension(new OpenOrchestraBackofficeExtension());

        $locator = new FileLocator(__DIR__ . '/Fixtures/config/');
        $loader = new YamlFileLoader($container, $locator);
        $loader->load($file . '.yml');
        $container->getCompilerPassConfig()->setOptimizationPasses(array());
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->compile();

        return $container;
    }
}
