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

namespace MonsieurBiz\SyliusBlogPlugin\Repository;

use Doctrine\ORM\QueryBuilder;
use MonsieurBiz\SyliusBlogPlugin\Entity\ArticleInterface;
use MonsieurBiz\SyliusBlogPlugin\Entity\AuthorInterface;
use MonsieurBiz\SyliusBlogPlugin\Entity\TagInterface;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Channel\Model\ChannelInterface;

class ArticleRepository extends EntityRepository implements ArticleRepositoryInterface
{
    public function createListQueryBuilderByType(string $localeCode, ?string $type): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('ba')
            ->addSelect('translation')
            ->leftJoin('ba.translations', 'translation', 'WITH', 'translation.locale = :localeCode')
            ->setParameter('localeCode', $localeCode)
        ;

        if (null !== $type) {
            $queryBuilder
                ->andWhere('ba.type = :type')
                ->setParameter('type', $type)
            ;
        }

        return $queryBuilder;
    }

    public function createShopListQueryBuilderByType(string $localeCode, ?string $type, ChannelInterface $channel, ?TagInterface $tag): QueryBuilder
    {
        $queryBuilder = $this->createListQueryBuilderByType($localeCode, $type)
            ->andWhere(':channel MEMBER OF ba.channels')
            ->andWhere('ba.enabled = true')
            ->andWhere('ba.state = :state')
            ->setParameter('channel', $channel)
            ->setParameter('state', ArticleInterface::STATE_PUBLISHED)
        ;

        if (null !== $tag) {
            $queryBuilder
                ->andWhere(':tag MEMBER OF ba.tags')
                ->setParameter('tag', $tag)
            ;
        }

        return $queryBuilder;
    }

    public function findAllEnabledAndPublishedByTag(string $localeCode, ?string $type, ChannelInterface $channel, TagInterface $tag, int $limit): array
    {
        return $this->createShopListQueryBuilderByType($localeCode, $type, $channel, $tag)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAllEnabledAndPublishedByTags(string $localeCode, ?string $type, ChannelInterface $channel, array $tags, int $limit): array
    {
        $queryBuilder = $this->createListQueryBuilderByType($localeCode, $type)
            ->andWhere(':channel MEMBER OF ba.channels')
            ->andWhere('ba.enabled = true')
            ->andWhere('ba.state = :state')
            ->setParameter('channel', $channel)
            ->setParameter('state', ArticleInterface::STATE_PUBLISHED)
            ->setMaxResults($limit)
            ->orderBy('ba.publishedAt', 'DESC')
        ;

        if (!empty($tags)) {
            $queryBuilder
                ->andWhere(':tags MEMBER OF ba.tags')
                ->setParameter('tags', $tags)
            ;
        }

        return $queryBuilder
            ->getQuery()
            ->getResult()
        ;
    }

    public function existsOneByTypeAndChannelAndSlug(string $slug, string $localeCode, ?string $type, ChannelInterface $channel, array $excludedArticles = []): bool
    {
        $queryBuilder = $this->createListQueryBuilderByType($localeCode, $type)
            ->andWhere('translation.slug = :slug')
            ->andWhere(':channel MEMBER OF ba.channels')
            ->andWhere('ba.type = :type')
            ->andWhere('ba.enabled = true')
            ->setParameter('slug', $slug)
            ->setParameter('channel', $channel)
            ->setParameter('type', $type)
        ;

        if (!empty($excludedArticles)) {
            $queryBuilder->andWhere('ba.id NOT IN (:excludedArticles)')
                ->setParameter('excludedArticles', $excludedArticles)
            ;
        }

        return (bool) $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function findOnePublishedBySlug(string $slug, string $localeCode, ?string $type, ChannelInterface $channel): ?ArticleInterface
    {
        return $this->createListQueryBuilderByType($localeCode, $type)
            ->andWhere('translation.slug = :slug')
            ->andWhere(':channel MEMBER OF ba.channels')
            ->andWhere('ba.enabled = true')
            ->andWhere('ba.state = :state')
            ->setParameter('slug', $slug)
            ->setParameter('channel', $channel)
            ->setParameter('state', ArticleInterface::STATE_PUBLISHED)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findAllEnabledAndPublishedByAuthor(string $localeCode, ?string $type, ChannelInterface $channel, AuthorInterface $author, int $limit): array
    {
        return $this->createListQueryBuilderByType($localeCode, $type)
            ->andWhere(':channel MEMBER OF ba.channels')
            ->andWhere('ba.enabled = true')
            ->andWhere('ba.state = :state')
            ->andWhere(':author MEMBER OF ba.authors')
            ->setParameter('channel', $channel)
            ->setParameter('state', ArticleInterface::STATE_PUBLISHED)
            ->setParameter('author', $author)
            ->addOrderBy('ba.publishedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findEnabledAndPublishedByIds(array $articleIds, string $localeCode, ?string $type, ChannelInterface $channel, ?int $number = null): array
    {
        $queryBuilder = $this->createShopListQueryBuilderByType($localeCode, $type, $channel, null)
            ->andWhere('ba.id in (:articleIds)')
            ->addOrderBy('ba.publishedAt', 'desc')
            ->setParameter('articleIds', $articleIds)
        ;

        if (null !== $number) {
            $queryBuilder->setMaxResults($number);
        }

        return $queryBuilder->getQuery()
            ->getResult()
        ;
    }
}
