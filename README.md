## WP Models
Wrapper class around WP Query to create models.

### Installations
Install the package through composer by running `composer require "vincentts/wp-models"`.

### Getting Started

Define model classes by extending `PostModel` or `TermModel`.


<b>Post Model</b>
```php
namespace Models;

use Vincentts\WpModels\PostModel;

class Post extends PostModel {
    
    protected $post_type = 'post';
    
}

```

<b>Term Model</b>
```php
namespace Models;

use Vincentts\WpModels\TermModel;

class Category extends TermModel {

    protected $taxonomy = 'category';
    
}
```

### Defining relationships

```php
namespace Models;

use Vincentts\WpModels\PostModel;
use Models\Category;

class Post extends PostModel {
    
    protected $post_type = 'post';

    public function categories() {
        return $this->has( Category::class );
    }

}
```

### Usage

<b>Getting posts with relationships</b>
```php
use Models\Post;

$posts = (new Post())->with(['categories'])->get();
```

<b>Including meta data</b>
> **Note** ACF `get_field` will be used if it exists. Otherwise it will use `get_post_meta`.
```php
use Models\Post;

$posts = (new Post())->meta(['summary', 'description'])->with(['categories'])->get();
```

There are also other methods similar to the arguments from `WP_Query`.