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

namespace MonsieurBiz\SyliusBlogPlugin\Menu;

use MonsieurBiz\SyliusBlogPlugin\Entity\ArticleInterface;
use MonsieurBiz\SyliusBlogPlugin\Entity\TagInterface;
use MonsieurBiz\SyliusBlogPlugin\Repository\TagRepositoryInterface;
use MonsieurBiz\SyliusMenuPlugin\Provider\AbstractUrlProvider;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\Assert;

if (class_exists(AbstractUrlProvider::class)) {
    class BlogListUrlProvider extends AbstractUrlProvider
    {
        public const PROVIDER_CODE = 'blog_list';

        protected string $code = self::PROVIDER_CODE;

        protected string $icon = 'tabler:news';

        protected int $priority = 35;

        public function __construct(
            RouterInterface $router,
            private TagRepositoryInterface $tagRepository,
            private TranslatorInterface $translator
        ) {
            parent::__construct($router);
        }

        protected function getResults(string $locale, string $search = ''): iterable
        {
            $queryBuilder = $this->tagRepository->createEnabledListQueryBuilderByType($locale, ArticleInterface::BLOG_TYPE);

            if (!empty($search)) {
                $queryBuilder
                    ->andWhere('translation.name LIKE :search OR translation.slug LIKE :search')
                    ->setParameter('search', '%' . $search . '%')
                ;
            }

            $queryBuilder->setMaxResults($this->getMaxResults());

            /** @phpstan-ignore-next-line */
            return $queryBuilder->getQuery()->getResult();
        }

        protected function addItemFromResult(object $result, string $locale): void
        {
            Assert::isInstanceOf($result, TagInterface::class);
            /** @var TagInterface $result */
            $result->setCurrentLocale($locale);
            $this->addItem(
                (string) $result->getName(),
                $this->router->generate('monsieurbiz_blog_tag_show', ['slug' => $result->getSlug(), '_locale' => $locale])
            );
        }

        public function getItems(string $locale, string $search = ''): array
        {
            parent::getItems($locale, $search);

            // Add item to link to all articles
            $firstItemLabel = $this->translator->trans('sylius.ui.all', [], null, $locale);
            if (empty($search) || false !== strpos($search, $firstItemLabel)) {
                $this->addItem($firstItemLabel, $this->router->generate('monsieurbiz_blog_index', ['_locale' => $locale]));
                // Add this last element to the beginning of the array
                $lastElement = array_pop($this->items);
                array_unshift($this->items, $lastElement);
            }

            return $this->items;
        }
    }
} else {
    class BlogListUrlProvider
    {
    }
}
