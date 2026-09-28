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

namespace MonsieurBiz\SyliusBlogPlugin\Form\Type;

use MonsieurBiz\SyliusBlogPlugin\Entity\TagInterface;
use MonsieurBiz\SyliusBlogPlugin\Repository\AuthorRepositoryInterface;
use MonsieurBiz\SyliusBlogPlugin\Repository\TagRepositoryInterface;
use MonsieurBiz\SyliusMediaManagerPlugin\Form\Type\ImageType;
use MonsieurBiz\SyliusMediaManagerPlugin\Form\Type\VideoType;
use Sylius\Bundle\ChannelBundle\Form\Type\ChannelChoiceType;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Bundle\ResourceBundle\Form\Type\ResourceTranslationsType;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Symfony\Bridge\Doctrine\Form\DataTransformer\CollectionToArrayTransformer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

final class ArticleType extends AbstractResourceType
{
    public function __construct(
        private LocaleContextInterface $localeContext,
        private AuthorRepositoryInterface $authorRepository,
        string $dataClass,
        array $validationGroups = [],
        #[Autowire('%monsieurbiz_blog.model.tag.class%')]
        private string $tagClass,
    ) {
        parent::__construct($dataClass, $validationGroups);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('enabled', CheckboxType::class, [
                'required' => false,
                'label' => 'sylius.ui.enabled',
            ])
            ->add('channels', ChannelChoiceType::class, [
                'multiple' => true,
                'expanded' => true,
                'label' => 'sylius.form.product.channels',
            ])
            ->add('authors', ChoiceType::class, [
                'label' => 'monsieurbiz_blog.form.article.authors',
                'multiple' => true,
                'choices' => $this->authorRepository->findAll(),
                'choice_label' => 'name',
                'choice_translation_domain' => false,
                'required' => false,
            ])
            ->add('tags', EntityType::class, [
                'label' => 'monsieurbiz_blog.form.article.tags',
                'required' => true,
                'multiple' => true,
                'class' => $this->tagClass,
                'query_builder' => function (TagRepositoryInterface $tagRepository) {
                    return $tagRepository->createListQueryBuilder($this->localeContext->getLocaleCode());
                },
                'choice_label' => function (TagInterface $tag) {
                    return $tag->getName();
                },
            ])
            ->add('image', ImageType::class, [
                'label' => 'monsieurbiz_blog.form.article.image',
                'required' => false,
            ])
            ->add('video', VideoType::class, [
                'label' => 'monsieurbiz_blog.form.article.video',
                'required' => false,
            ])
            ->add('thumbnailImage', ImageType::class, [
                'label' => 'monsieurbiz_blog.form.article.thumbnail_image',
                'help' => 'monsieurbiz_blog.form.article.thumbnail_image_help',
                'required' => false,
            ])
            ->add('translations', ResourceTranslationsType::class, [
                'entry_type' => ArticleTranslationType::class,
            ])
        ;

        $builder->get('authors')->addModelTransformer(new CollectionToArrayTransformer());
    }

    public function getBlockPrefix(): string
    {
        return 'monsieurbiz_blog_article';
    }
}
