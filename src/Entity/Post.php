<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PostRepository::class,)]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;



    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;


    #[ORM\Column(type: "integer")]
    private int $likes = 0; 
    

    #[ORM\OneToMany(targetEntity: Comments::class,  mappedBy: 'post',orphanRemoval:true)]
    private Collection $comments;



    #[ORM\Column(length: 255)]
    private ?string $image = null;

   



    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTime();
        }
    }
    

    public function getId(): ?int
    {
        return $this->id;
    }

    

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        if ($content === null) {
            $content='';
        }
    
        $this->content = $content;
        return $this;
    }

    public function getLikes(): int
    {
        return $this->likes;
    }
    public function like(): self
{
    $this->likes++;
    return $this;
}

    public function addLike(): self
    {
        $this->likes++;
        return $this;
    }

    public function removeLike(): self
    {
        if ($this->likes > 0) {
            $this->likes--;
        }
        return $this;
    }

   
    

      /**
     * @return Collection<int, Comments>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comments $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setPost($this);
        }

        return $this;
    }

    public function removeComment(Comments $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getPost() === $this) {
                $comment->setPost(null);
            }
        }

        return $this;
    }

   
    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

   

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
