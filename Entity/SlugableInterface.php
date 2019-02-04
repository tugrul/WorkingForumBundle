<?php

namespace Yosimitso\WorkingForumBundle\Entity;


interface SlugableInterface
{
    public function setSlug(?string $slug);
    public function getSlug(): ?string;
    public function getSlugProvider(): string;
}