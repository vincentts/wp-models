<?php

namespace Vincentts\WpModels\Builder;

trait PostArgsBuilder {
    protected $single = false;
    protected $post_args = [];

    public function find( int $id ) {
        $this->post_args['p'] = $id;
        $this->single = true;
        
        return $this;
    }

    public function name( string $slug ) {
        $this->post_args['name'] = $slug;
       
        return $this;
    }

    public function names_in( array $slugs ) {
        $this->post_args['post_name__in'] = $slugs;
        
        return $this;
    }

    public function in( array $post_ids ) {
        $this->post_args['post__in'] = $post_ids;
        
        return $this;
    }

    public function notIn( array $post_ids ) {
        $this->post_args['post__not_in'] = $post_ids;
        
        return $this;
    }

    public function parent( int $post_id ) {
        $this->post_args['post_parent'] = $post_id;
        
        return $this;
    }

    public function parentIn( array $post_ids ) {
        $this->post_args['post_parent__in'] = $post_ids;
        
        return $this;
    }

    public function parentNotIn( array $post_ids ) {
        $this->post_args['post_parent__not_in'] = $post_ids;
        
        return $this;
    }

    protected function getPostArgs() {
        return array_filter( $this->post_args, function( $arg ) {
            return ! empty( $arg );
        } );
    }
}