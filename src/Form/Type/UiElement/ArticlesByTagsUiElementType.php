<?php

/*
 * This file is part of Monsieur Biz' Blog plugin for Sylius.
 *
 * (c) Monsieur Biz <sylius@monsieurbiz.com>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MonsieurBiz\SyliusBlogPlugin\Form\Type\UiElement;

use MonsieurBiz\SyliusBlogPlugin\Entity\ArticleInterface;
use MonsieurBiz\SyliusBlogPlugin\Form\Type\ArticlesDisplayType;
use MonsieurBiz\SyliusBlogPlugin\Repository\TagRepositoryInterface;
use MonsieurBiz\SyliusRichEditorPlugin\Attribute\AsUiElement;
use MonsieurBiz\SyliusRichEditorPlugin\Attribute\TemplatesUiElement;
use MonsieurBiz\SyliusRichEditorPlugin\Form\Type\LinkType;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[AsUiElement(
    code: 'monsieurbiz_blog.articles_by_tags_ui_element',
    icon: 'tabler:tags',
    title: 'monsieurbiz_blog.ui_element.articles_by_tags_ui_element.title',
    description: 'monsieurbiz_blog.ui_element.articles_by_tags_ui_element.description',
    uiElement: 'MonsieurBiz\SyliusBlogPlugin\UiElement\ArticlesByTagsUiElement',
    templates: new TemplatesUiElement(
        adminRender: '@MonsieurBizSyliusBlogPlugin/admin/ui_element/articles_by_tags.html.twig',
        frontRender: '@MonsieurBizSyliusBlogPlugin/shop/ui_element/articles_by_tags.html.twig',
    ),
    wireframe: 'articles-by-tags',
    tags: ['blog', 'articles-by-tags'],
)]
class ArticlesByTagsUiElementType extends AbstractType
{
    public function __construct(
        private readonly TagRepositoryInterface $tagRepository,
        private readonly LocaleContextInterface $localeContext,
        private bool $enableCaseStudies,
        #[Autowire('%monsieurbiz_blog.model.tag.class%')]
        private string $tagClass,
    ) {
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'monsieurbiz_blog.ui_element.articles_by_tags_ui_element.fields.title',
                'required' => false,
            ])
            ->add('display', ArticlesDisplayType::class, [
                'label' => false, // already defined in the ArticlesDisplayType
            ])
            ->add('tags', EntityType::class, [
                'label' => 'monsieurbiz_blog.ui_element.articles_by_tags_ui_element.fields.tags',
                'required' => false,
                'class' => $this->tagClass,
                'choice_label' => fn (Tag $tag) => $tag->getName(),
                'choice_value' => fn (?Tag $tag) => $tag?->getId(),
                'query_builder' => function (TagRepositoryInterface $tagRepository) {
                    return $tagRepository->createListQueryBuilder(
                        $this->localeContext->getLocaleCode(),
                    )->orderBy('translation.name');
                },
                'multiple' => true,
            ])
        ;

        if ($this->enableCaseStudies) {
            $builder
                ->add('typeFilter', ChoiceType::class, [
                    'label' => 'monsieurbiz_blog.ui_element.articles_by_tags_ui_element.fields.type_filter',
                    'required' => true,
                    'choices' => [
                        'monsieurbiz_blog.ui_element.articles_by_tags_ui_element.choices.type_filter.all' => null,
                        'monsieurbiz_blog.ui_element.articles_by_tags_ui_element.choices.type_filter.blog' => ArticleInterface::BLOG_TYPE,
                        'monsieurbiz_blog.ui_element.articles_by_tags_ui_element.choices.type_filter.case_study' => ArticleInterface::CASE_STUDY_TYPE,
                    ],
                ])
            ;
        }

        $builder
            ->add('limit', IntegerType::class, [
                'label' => 'monsieurbiz_blog.ui_element.articles_by_tags_ui_element.fields.limit',
                'help' => 'monsieurbiz_blog.ui_element.articles_by_tags_ui_element.help.limit',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\GreaterThan(0),
                ],
            ])
            ->add('buttonLabel', TextType::class, [
                'label' => 'monsieurbiz_blog.ui_element.articles_by_tags_ui_element.fields.button_label',
                'required' => false,
            ])
            ->add('buttonUrl', LinkType::class, [
                'label' => 'monsieurbiz_blog.ui_element.articles_by_tags_ui_element.fields.button_url',
                'required' => false,
            ])
        ;

        $builder->get('tags')->addModelTransformer(
            new CallbackTransformer(
                function ($tagsAsArray) {
                    return $this->tagRepository->findBy(['id' => $tagsAsArray ?? []]);
                },
                function ($tagsAsString) {
                    $tags = [];
                    foreach ($tagsAsString as $tag) {
                        $tags[] = $tag->getId();
                    }

                    return $tags;
                }
            ),
        );
    }
}
