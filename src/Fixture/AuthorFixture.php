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
use MonsieurBiz\SyliusBlogPlugin\Fixture\Factory\AuthorFixtureFactory;
use Sylius\Bundle\CoreBundle\Fixture\AbstractResourceFixture;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class AuthorFixture extends AbstractResourceFixture
{
    public function __construct(
        EntityManagerInterface $blogAuthorManager,
        AuthorFixtureFactory $exampleFactory
    ) {
        parent::__construct($blogAuthorManager, $exampleFactory);
    }

    public function getName(): string
    {
        return 'monsieubiz_blog_author';
    }

    protected function configureResourceNode(ArrayNodeDefinition $resourceNode): void
    {
        /** @phpstan-ignore-next-line */
        $resourceNode
            ->children()
                ->scalarNode('name')->cannotBeEmpty()->end()
                ->scalarNode('image')->defaultNull()->end()
            ->end()
        ;
    }
}
