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

namespace MonsieurBiz\SyliusBlogPlugin\Fixture;

use Doctrine\ORM\EntityManagerInterface;
use MonsieurBiz\SyliusBlogPlugin\Fixture\Factory\ArticleFixtureFactory;
use Sylius\Bundle\CoreBundle\Fixture\AbstractResourceFixture;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class ArticleFixture extends AbstractResourceFixture
{
    public function __construct(
        EntityManagerInterface $blogArticleManager,
        ArticleFixtureFactory $exampleFactory
    ) {
        parent::__construct($blogArticleManager, $exampleFactory);
    }

    public function getName(): string
    {
        return 'monsieubiz_blog_article';
    }

    protected function configureResourceNode(ArrayNodeDefinition $resourceNode): void
    {
        /** @phpstan-ignore-next-line */
        $resourceNode
            ->children()
                ->booleanNode('enabled')->defaultTrue()->end()
                ->scalarNode('type')->cannotBeEmpty()->end()
                ->scalarNode('image')->defaultNull()->end()
                ->scalarNode('video')->defaultNull()->end()
                ->scalarNode('thumbnailImage')->defaultNull()->end()
                ->arrayNode('tags')
                    ->scalarPrototype()->end()
                ->end()
                ->scalarNode('is_published')->defaultTrue()->end()
                ->scalarNode('publish_date')->cannotBeEmpty()->end()
                ->arrayNode('authors')
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('translations')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('title')->cannotBeEmpty()->end()
                            ->scalarNode('slug')->cannotBeEmpty()->end()
                            ->scalarNode('description')->cannotBeEmpty()->end()
                            ->scalarNode('content')->cannotBeEmpty()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
