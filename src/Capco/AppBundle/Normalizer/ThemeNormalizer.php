<?php

namespace Capco\AppBundle\Normalizer;

use Capco\AppBundle\Entity\Theme;
use Capco\AppBundle\Twig\MediaExtension;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;

class ThemeNormalizer implements
    NormalizerInterface,
    SerializerAwareInterface,
    CacheableSupportsMethodInterface
{
    use SerializerAwareTrait;
    private $router;
    private ObjectNormalizer $normalizer;
    private $mediaExtension;

    public function __construct(
        UrlGeneratorInterface $router,
        ObjectNormalizer $normalizer,
        MediaExtension $mediaExtension
    ) {
        $this->router = $router;
        $this->normalizer = $normalizer;
        $this->mediaExtension = $mediaExtension;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $groups =
            isset($context['groups']) && \is_array($context['groups']) ? $context['groups'] : [];
        $data = $this->normalizer->normalize($object, $format, $context);

        if (
            \in_array('ElasticsearchEvent', $groups, true) ||
            \in_array('ElasticsearchProposalNestedTheme', $groups, true) ||
            \in_array('ElasticsearchProjectNestedTheme', $groups, true) ||
            \in_array('ElasticsearchEventNestedTheme', $groups, true)
        ) {
            return $data;
        }

        try {
            $data['media']['url'] = $this->mediaExtension->path($object->getMedia(), 'slider');
        } catch (RouteNotFoundException $e) {
            // Avoid some SonataMedia problems
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Theme;
    }
}
