<?php

namespace Vincentts\WpModels\Builder;

trait PaginationArgsBuilder {
    protected $page = 0;
    protected $posts_per_page = -1;
    protected $offset = 0;
    protected $ignore_sticky_posts = false;

    public function page( int $page_no ) {
        $this->page = $page_no;
        
        return $this;
    }

    public function perPage( int $per_page ) {
        $this->posts_per_page = $per_page;
        
        return $this;
    }

    public function offset( int $offset ) {
        $this->offset = $offset;
        
        return $this;
    }

    public function ignoreSticky() {
        $this->ignore_sticky_posts = true;
        
        return $this;
    }

    protected function getPaginationArgs() {
        return [
            'posts_per_page' => $this->posts_per_page,
            'ignore_sticky_posts' => $this->ignore_sticky_posts,
            'offset' => $this->offset > 0 ? $this->offset : 0,
            'page'=> $this->page > 0 ? $this->page : 0,
        ];
    }
}