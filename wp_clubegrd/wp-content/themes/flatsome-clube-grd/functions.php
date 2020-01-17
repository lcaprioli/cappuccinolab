<?php
// Add custom Theme Functions here
function load_child_stylesheet() {
    wp_enqueue_style( 'geral', get_stylesheet_directory_uri() . '/css/geral.css','','1.0.1');
    wp_enqueue_style( 'cabecalho', get_stylesheet_directory_uri() . '/css/cabecalho.css','','1.0.1' );
    wp_enqueue_style( 'rodape', get_stylesheet_directory_uri() . '/css/rodape.css','','1.0.1' );
    wp_enqueue_style( 'home', get_stylesheet_directory_uri() . '/css/home.css','','1.0.1' );
    wp_enqueue_style( 'contratar', get_stylesheet_directory_uri() . '/css/contratar-plano.css','','1.0.1' );
    wp_enqueue_style( 'blog', get_stylesheet_directory_uri() . '/css/blog.css','','1.0.1' );
    wp_enqueue_style( 'form-beneficios', get_stylesheet_directory_uri() . '/css/form-beneficios.css','','1.0.1' );
}
add_action( 'wp_enqueue_scripts', 'load_child_stylesheet', 999);