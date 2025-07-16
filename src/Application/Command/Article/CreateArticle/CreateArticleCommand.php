<?php

declare(strict_types=1);

namespace App\Application\Command\Article\CreateArticle;

use App\Application\Bus\Command\CommandInterface;
use Symfony\Component\Validator\Constraints as Assert;

class CreateArticleCommand implements CommandInterface
{
    public function __construct(
        #[Assert\NotBlank]
        public string $title,
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $content,
        #[Assert\NotBlank]
        #[Assert\Email]
        public string $authorEmail,
    ) {
    }
}
