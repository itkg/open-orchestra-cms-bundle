<?php

namespace OpenOrchestra\BackofficeBundle\Form\Type\Component;

use OpenOrchestra\Backoffice\NavigationPanel\Strategies\AdministrationPanelStrategy;
use OpenOrchestra\ModelInterface\Repository\KeywordRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use OpenOrchestra\BackofficeBundle\Form\DataTransformer\EmbedKeywordsToKeywordsTransformer;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\OptionsResolver\Options;

/**
 * Class KeywordsChoiceType
 */
class KeywordsChoiceType extends AbstractType
{
    protected $keywordsTransformer;
    protected $keywordRepository;
    protected $router;

    /**
     * @param EmbedKeywordsToKeywordsTransformer $keywordsTransformer
     * @param KeywordRepositoryInterface         $keywordRepository
     * @param RouterInterface                    $router
     * @param AuthorizationCheckerInterface      $authorizationChecker
     */
    public function __construct(
        EmbedKeywordsToKeywordsTransformer $keywordsTransformer,
        KeywordRepositoryInterface $keywordRepository,
        RouterInterface $router,
        AuthorizationCheckerInterface $authorizationChecker
    )
    {
        $this->keywordsTransformer = $keywordsTransformer;
        $this->keywordRepository = $keywordRepository;
        $this->router = $router;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['embedded']) {
            $builder->addModelTransformer($this->keywordsTransformer);
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $isGranted = $this->authorizationChecker->isGranted(AdministrationPanelStrategy::ROLE_ACCESS_CREATE_KEYWORD);
        $attr = function(Options $options) {
            $default = array(
                'class' => 'select2',
                'data-tags' => $this->getTags(),
                'data-check' => $this->router->generate('open_orchestra_api_check_keyword', array()),
            );
            return array_replace($default, $options['new_attr']);
        };

        $resolver->setDefaults(array(
            'embedded' => true,
            'attr' => $attr,
            'new_attr' => array('data-authorize-new' => ($isGranted) ? "1" : "0")
        ));
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return 'text';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'oo_keywords_choice';
    }

    /**
     * @return string
     */
    protected function getTags()
    {
        $keywords = $this->keywordRepository->findAll();
        $tags = array();
        foreach ($keywords as $tag) {
            $tags[] = $tag->getLabel();
        }

        return json_encode($tags);
    }
}
