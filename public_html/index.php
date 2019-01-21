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

    set_include_path(__DIR__.'/../include:'.__DIR__.'/../vendor:'.get_include_path());
    
    include 'autoload.php';
    
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
    
    $postsByHost = [];
    foreach($hosts as $host) {
        $errors = [];
        
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
        $posts = json_decode($json);
        if ($posts === null) {
            $errors []= 'No data received from site.';
        }
        

        $postsByHost[$host] = [
            'hostName' => $host,
            'posts' => $posts,
            'errors' => $errors,
        ];        
    }
    
    $loader = new Twig_Loader_Filesystem(getcwd().'/../template');
    $twig = new Twig_Environment($loader, [
//         'cache' => sys_get_temp_dir(),
    ]);
    
    echo $twig->render(
        'index.html',
        [
            'postsByHost' => $postsByHost
        ]
    );