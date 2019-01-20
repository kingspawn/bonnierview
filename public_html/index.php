<?php
    define('PAGE', 1);
    define('PER_PAGE', 10);
    
    $hosts = [
//         'finnsinte.com',
        'teknikensvarld.se',
        'alltommat.se',
        'skonahem.com',
        'mama.nu',
    ];

    if (!function_exists('http_build_url')) {
        function http_build_url(array $url) : string {
            $scheme = rtrim($url['scheme'] ?? 'http', ':/');
            
            if (!array_key_exists('host', $url)) {
                throw new InvalidArgumentException("Missing key 'host'");
            }
            $host = trim($url['host'], '/');
            
            if (!array_key_exists('path', $url)) {
                throw new InvalidArgumentException("Missing key 'path'");
            }
            $path = ltrim($url['path'], '/');
            
            if (array_key_exists('query', $url)) {
                if (is_string($url['query'])) {
                    $query = $url['query'];
                }
                else if (is_array($url['query'])) {
                    $query = http_build_query($url['query']);
                }
            }
            else {
                $query = '';
            }
            
            return "$scheme://$host/$path".($query!='' ? "?$query" : '');
        }
    }
    
    foreach($hosts as $host) {
        $url = http_build_url([
            'scheme' => 'http',
            'host' => $host,
            
            'path' => '/wp-json/wp/v2/posts',
            'query' => http_build_query([
                'page' => PAGE,
                'per_page' => PER_PAGE
            ])
        ]);
        
        $json = file_get_contents($url);
        
        var_dump($json); exit();
        
        $posts = json_decode($json);

        echo '<h1>'.$host.'</h1>';
        echo '<ul>';
        foreach(array_values($posts) as $i => $post) {
            echo '<a href="'.$post->link.'">'.
                '<img src="'.$post->featured_image->src.'" alt="Artikel #'.($i+1).' för '.$host.'" />'.
                '</a>';
        }
        echo '</ul>';
    }