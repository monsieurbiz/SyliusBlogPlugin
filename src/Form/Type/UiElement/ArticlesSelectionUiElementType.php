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

use MonsieurBiz\SyliusBlogPlugin\Form\Type\ArticlesDisplayType;
use MonsieurBiz\SyliusBlogPlugin\Form\Type\ArticleSelectionElementType;
use MonsieurBiz\SyliusRichEditorPlugin\Attribute\AsUiElement;
use MonsieurBiz\SyliusRichEditorPlugin\Attribute\TemplatesUiElement;
use MonsieurBiz\SyliusRichEditorPlugin\Form\Type\LinkType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\UX\LiveComponent\Form\Type\LiveCollectionType;

#[AsUiElement(
    code: 'monsieurbiz_blog.articles_selection_ui_element',
    icon: 'tabler:news',
    title: 'monsieurbiz_blog.ui_element.articles_selection_ui_element.title',
    description: 'monsieurbiz_blog.ui_element.articles_selection_ui_element.description',
    uiElement: 'MonsieurBiz\SyliusBlogPlugin\UiElement\ArticlesSelectionUiElement',
    templates: new TemplatesUiElement(
        adminRender: '@MonsieurBizSyliusBlogPlugin/admin/ui_element/articles_selection.html.twig',
        frontRender: '@MonsieurBizSyliusBlogPlugin/shop/ui_element/articles_selection.html.twig',
    ),
    wireframe: 'articles-selection',
    tags: ['blog', 'articles-selection'],
)]
class ArticlesSelectionUiElementType extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'monsieurbiz_blog.ui_element.articles_selection_ui_element.fields.title',
                'required' => false,
            ])
            ->add('display', ArticlesDisplayType::class, [
                'label' => false, // already defined in the ArticlesDisplayType
            ])
            ->add('articles', LiveCollectionType::class, [
                'label' => 'monsieurbiz_blog.ui_element.articles_selection_ui_element.fields.articles',
                'entry_type' => ArticleSelectionElementType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'button_add_options' => [
                    'label' => 'sylius.ui.add',
                ],
                'button_delete_options' => [
                    'label' => 'sylius.ui.delete',
                    'attr' => [
                        'class' => 'btn-outline-danger',
                    ],
                ],
                'attr' => [
                    'class' => 'row row-cols-1 row-cols-sm-2',
                ],
                'entry_options' => [
                    'label' => false,
                    'attr' => [
                        'class' => 'p-3 bg-gray-300 border rounded col',
                    ],
                ],
                'constraints' => [
                    new Assert\Count([
                        'min' => 1,
                    ]),
                ],
            ])
            ->add('buttonLabel', TextType::class, [
                'label' => 'monsieurbiz_blog.ui_element.articles_selection_ui_element.fields.button_label',
                'required' => false,
            ])
            ->add('buttonUrl', LinkType::class, [
                'label' => 'monsieurbiz_blog.ui_element.articles_selection_ui_element.fields.button_url',
                'required' => false,
            ])
        ;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        usort($view['articles']->children, function (FormView $articleA, FormView $articleB) {
            return match (true) {
                !$articleA->offsetExists('position') => -1,
                !$articleB->offsetExists('position') => 1,
                default => $articleA['position']->vars['data'] <=> $articleB['position']->vars['data']
            };
        });
    }
}
