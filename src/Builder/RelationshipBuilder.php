<?php

namespace Vincentts\WpModels\Builder;

use WP_Post;
use Vincentts\WpModels\PostModel;
use Vincentts\WpModels\TermModel;
use Exception;

trait RelationshipBuilder {
    protected $object_id;
    protected $relations = [];
    protected $meta = [];

    protected function has( string $model_class, string $field = null ) {
        if ( ! class_exists( $model_class ) ) {
            throw new Exception( 'Model does not exist' );
        }

        $model = new $model_class();
        
        if ( $this instanceof PostModel ) {
            if ( $model instanceof PostModel ) {
                if ( function_exists( 'get_field' ) ) {
                    $relations = get_field( $field, $this->object_id );
                    if ( $relations ) {
                        $post_ids = array_map( function( $post ) {
                            if ( $post instanceof WP_Post ) {
                                return $post->ID;
                            }
                            return $post;
                        }, $relations );
    
                        return $model->in( $post_ids );
                    }

                    return $model->in( [-1] );
                }
            }

            if ( $model instanceof TermModel ) {
                return $model->for( [ $this->object_id ] );
            }
        }
  
        if ( $this instanceof TermModel ) {
            if ( $model instanceof PostModel ) {
                return $model->taxFilter( $this->taxonomy, [ $this->object_id ] );
            }

            if ( $model instanceof TermModel ) {
                throw new Exception( "Terms to terms relationship is not supported yet.");
            }
        }
    }

    protected function belongsTo( string $model_class, string $field = null ) {
        if ( ! class_exists( $model_class ) ) {
            throw new Exception( 'Model does not exist' );
        }

        $model = new $model_class();

        if ( $this instanceof PostModel ) {
            if ( $model instanceof PostModel ) {
                if ( function_exists( 'get_field' ) ) {
                    return $model->metaFilter( $field, '"'. $this->object_id . '"', "string", 'LIKE' );
                }
            }

            if ( $model instanceof TermModel ) {
                return $model->for( [ $this->object_id ] );
            }
        }

        if ( $this instanceof TermModel ) {
            if ( $model instanceof PostModel ) {
                return $model->taxFilter( $this->taxonomy, [ $this->object_id ] );
            }

            if ( $this instanceof TermModel ) {
                throw new Exception( "Terms to terms relationship is not supported yet." );
            }
        }
    }

    public function with( array $relations ) {
        $this->relations = $relations;
        
        return $this;
    }

    public function meta( array $meta_keys = [] ) {
        $this->meta = $meta_keys;

        return $this;
    }

    protected function getMetaData() {
        $meta_data = [];

        if ( ! empty( $this->meta ) ) {
            foreach( $this->meta as $key ) {
                if ( function_exists( 'get_field' ) ) {
                    if ( $this instanceof PostModel ) {
                        $meta_data[$key] = get_field( $key, $this->object_id );
                    } else {
                        $meta_data[$key] = get_field( $key, $this->taxonomy . '_' . $this->object_id );
                    }
                } else {
                    if ( $this instanceof PostModel ) {
                        $meta_data[$key] = get_post_meta( $this->object_id, $key );
                    }

                    if ( $this instanceof TermModel ) {
                        $meta_data[$key] = get_term_meta( $this->object_id, $key );
                    }
                }
            }
        }

        return $meta_data;
    }

    protected function getRelationships() {
        $relations = [];

        foreach ( $this->relations as $relation_key => $callback ) {
            $relation = is_numeric( $relation_key ) ? $callback : $relation_key;
            
            if ( method_exists( $this, $relation ) ) {
                $relations[ $relation ] = is_callable( $callback ) ? 
                                            $callback( $this->$relation() ) : 
                                            $this->$relation()->get();
            }
        }

        return $relations;
    }
}