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

use MonsieurBiz\SyliusBlogPlugin\Entity\ArticleInterface;
use MonsieurBiz\SyliusBlogPlugin\Repository\ArticleRepositoryInterface;
use Sylius\Bundle\ResourceBundle\Form\DataTransformer\ResourceToIdentifierTransformer;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\ReversedTransformer;
use Symfony\Component\Validator\Constraints as Assert;

final class ArticleSelectionElementType extends AbstractType
{
    public function __construct(
        private readonly ArticleRepositoryInterface $articleRepository,
        private readonly ChannelContextInterface $channelContext,
        private readonly LocaleContextInterface $localeContext,
        private bool $enableCaseStudies,
        #[Autowire('%monsieurbiz_blog.model.article.class%')]
        private string $articleClass,
    ) {
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('article', EntityType::class, [
                'class' => $this->articleClass,
                'label' => 'monsieurbiz_blog.ui_element.articles_selection_ui_element.fields.article',
                'choice_label' => fn (Article $article) => '[' . $article->getType() . '] ' . $article->getTitle(),
                'choice_value' => fn (?Article $article) => $article?->getId(),
                'required' => true,
                'autocomplete' => true,
                'query_builder' => function (ArticleRepositoryInterface $articleRepository) {
                    $queryBuilder = $articleRepository->createShopListQueryBuilderByType(
                        $this->localeContext->getLocaleCode(),
                        null,
                        $this->channelContext->getChannel(),
                        null
                    );
                    if (!$this->enableCaseStudies) {
                        $queryBuilder
                            ->andWhere('ba.type != :caseStudyType')
                            ->setParameter('caseStudyType', ArticleInterface::CASE_STUDY_TYPE)
                        ;
                    }
                    $queryBuilder
                        ->orderBy('ba.type')
                        ->addOrderBy('translation.title')
                    ;

                    return $queryBuilder;
                },
            ])
            ->add('position', IntegerType::class, [
                'label' => 'monsieurbiz_blog.ui_element.articles_selection_ui_element.fields.position',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\GreaterThan(0),
                ],
            ])
        ;

        $builder->get('article')->addModelTransformer(
            new ReversedTransformer(new ResourceToIdentifierTransformer($this->articleRepository, 'id')),
        );
    }
}
