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

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use MonsieurBiz\SyliusBlogPlugin\Entity\ArticleInterface;
use MonsieurBiz\SyliusBlogPlugin\Entity\TagInterface;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class TagRepository extends EntityRepository implements TagRepositoryInterface
{
    public function findRootNodes(): array
    {
        return $this->createQueryBuilder('o')
            ->addOrderBy('o.position')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findHydratedRootNodes(): array
    {
        $this->createQueryBuilder('o')
            ->select(['o', 'ot'])
            ->leftJoin('o.translations', 'ot')
            ->getQuery()
            ->getResult()
        ;

        return $this->findRootNodes();
    }

    public function createListQueryBuilder(string $localeCode): QueryBuilder
    {
        return $this->createQueryBuilder('bc')
            ->addSelect('translation')
            ->leftJoin('bc.translations', 'translation', 'WITH', 'translation.locale = :localeCode')
            ->setParameter('localeCode', $localeCode)
        ;
    }

    public function createEnabledListQueryBuilder(string $localeCode): QueryBuilder
    {
        return $this->createListQueryBuilder($localeCode)
            ->join('bc.articles', 'articles')
            ->andWhere('bc.enabled = true')
            ->andWhere('articles.enabled = true')
            ->andWhere('articles.state = :state')
            ->setParameter('state', ArticleInterface::STATE_PUBLISHED)
        ;
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findOneByName(string $name, string $localeCode): ?TagInterface
    {
        return $this->createListQueryBuilder($localeCode)
            ->andWhere('translation.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findOneBySlug(string $slug, string $localeCode): ?TagInterface
    {
        return $this->createListQueryBuilder($localeCode)
            ->andWhere('translation.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function createEnabledListQueryBuilderByType(string $localeCode, string $type): QueryBuilder
    {
        return $this->createEnabledListQueryBuilder($localeCode)
            ->andWhere('articles.type = :type')
            ->setParameter('type', $type)
        ;
    }
}
