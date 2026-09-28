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

namespace MonsieurBiz\SyliusBlogPlugin\Fixture\Factory;

use Behat\Transliterator\Transliterator;
use Faker\Factory;
use Faker\Generator;
use MonsieurBiz\SyliusBlogPlugin\Entity\TagInterface;
use MonsieurBiz\SyliusBlogPlugin\Entity\TagTranslationInterface;
use Sylius\Bundle\CoreBundle\Fixture\Factory\AbstractExampleFactory;
use Sylius\Component\Locale\Model\LocaleInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TagFixtureFactory extends AbstractExampleFactory
{
    private OptionsResolver $optionsResolver;

    private OptionsResolver $translationOptionsResolver;

    private Generator $faker;

    public function __construct(
        private FactoryInterface $tagFactory,
        private FactoryInterface $tagTranslationFactory,
        private RepositoryInterface $localeRepository,
    ) {
        $this->faker = Factory::create();

        $this->optionsResolver = new OptionsResolver();
        $this->configureOptions($this->optionsResolver);

        $this->translationOptionsResolver = new OptionsResolver();
        $this->configureTranslationOptions($this->translationOptionsResolver);
    }

    public function create(array $options = []): TagInterface
    {
        $options = $this->optionsResolver->resolve($options);

        /** @var TagInterface $tag */
        $tag = $this->tagFactory->createNew();
        $tag->setEnabled($options['enabled']);
        $this->createTranslations($tag, $options);

        return $tag;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('enabled', function (Options $options): bool {
                return $this->faker->boolean(80);
            })

            ->setDefault('translations', function (OptionsResolver $translationResolver): void {
                $translationResolver->setDefaults($this->configureDefaultTranslations());
            })
            ->setAllowedTypes('translations', ['array'])
        ;
    }

    private function createTranslations(TagInterface $tag, array $options): void
    {
        foreach ($options['translations'] as $localeCode => $translation) {
            $translation = $this->translationOptionsResolver->resolve($translation);
            /** @var TagTranslationInterface $tagTranslation */
            $tagTranslation = $this->tagTranslationFactory->createNew();
            $tagTranslation->setLocale($localeCode);
            $tagTranslation->setName($translation['name']);
            $slug = $translation['slug'] ?? Transliterator::transliterate(str_replace('\'', '-', $translation['name']));
            $tagTranslation->setSlug($slug);

            $tag->addTranslation($tagTranslation);
        }
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function configureTranslationOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('name', fn (Options $options): string => /** @phpstan-ignore-line */ $this->faker->words(3, true))
            ->setDefault('slug', null)
        ;
    }

    private function configureDefaultTranslations(): array
    {
        $translations = [];
        $locales = $this->localeRepository->findAll();
        /** @var LocaleInterface $locale */
        foreach ($locales as $locale) {
            $translations[$locale->getCode()] = [];
        }

        return $translations;
    }
}
