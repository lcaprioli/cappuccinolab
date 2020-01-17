<?php
add_action( 'woocommerce_product_options_advanced', 'misha_adv_product_options');
function misha_adv_product_options(){
 
	echo '<div class="options_group">';
 
	woocommerce_wp_checkbox( array(
		'id'      => 'has_result',
		'value'   => get_post_meta( get_the_ID(), 'has_result', true ),
		'label'   => 'Tem resultado?',
		'desc_tip' => true,
		'description' => 'Define se este produto tem resultado ou não.',
	) );

    $posts = get_posts(array(
		'post_type' => 'local'
    ));
    
    global $post;

	if (count($posts) > 0){

	    foreach($posts as $postLocal)
		{
            $options[$postLocal->ID] = $postLocal->post_title;
            $value = get_post_meta( $post->ID, "local", true);
		}
	}

    woocommerce_wp_select( array(
        'id'      => 'local',
        'label'   => __( 'Local', 'woocommerce' ),
        'options' =>  $options, //this is where I am having trouble
        'value'   => $value,
    ) );

    $package_options["single"] = "Unitário";
    $package_options["tablet"] = "Comprimido";
    $package_options["liquid"] = "Gotas";

    woocommerce_wp_select( array(
        'id'      => 'package_type',
        'label'   => __( 'Tipo de embalagem', 'woocommerce' ),
        'options' =>   $package_options, //this is where I am having trouble
        'value'   => get_post_meta( get_the_ID(), 'package_type', true ),
    ) );

    woocommerce_wp_text_input( array(
        'id'      => 'package_quantity',
        'label'   => __( 'Quantidade da embalagem', 'woocommerce' ),
        'type' => 'text',
        'data_type' => 'decimal',
        'value'   => get_post_meta( get_the_ID(), 'package_quantity', true ),
    ) );

}
 
 
add_action( 'woocommerce_process_product_meta', 'misha_save_fields', 10, 2 );
function misha_save_fields( $id, $post ){
 
	if( !empty( $_POST['has_result'] ) ) {
		update_post_meta( $id, 'has_result', $_POST['has_result'] );
	} else {
		delete_post_meta( $id, 'has_result' );
    }
    
    if( !empty( $_POST['local'] ) ) {
		update_post_meta( $id, 'local', $_POST['local'] );
	} else {
		delete_post_meta( $id, 'local' );
    }
    
    if( !empty( $_POST['package_type'] ) ) {
		update_post_meta( $id, 'package_type', $_POST['package_type'] );
	} else {
		delete_post_meta( $id, 'package_type' );
	}
 
    if( !empty( $_POST['package_quantity'] ) ) {
		update_post_meta( $id, 'package_quantity', $_POST['package_quantity'] );
	} else {
		delete_post_meta( $id, 'package_quantity' );
	}
}

function createProduct($product_id){

    $product = wc_get_product( $product_id );
    // do something with this product

    $product_data = productCreateJSON($product_id,$product);

    if(get_post_meta($product_id, 'firebaseID', true))
    {
       $firebaseID = get_post_meta($product_id, 'firebaseID', true);
       editDataFirebase("products",$firebaseID,$product_data);
    }
    else
    {
       $newid = saveDataFirebase("products",$product_data);
       update_post_meta($product_id, "firebaseID", $newid);
    }

}

add_action( 'woocommerce_update_product', 'wooChangeOrAddProduct', 10, 1 );
function wooChangeOrAddProduct( $product_id ) {
     
    createProduct($product_id);

	}
 

    
function beforeDeleteProduct($post_id){
    
	$firebaseID = get_post_meta($post_id, 'firebaseID', true);
	if($firebaseID)
	{
		deleteDataFirebase("products",$firebaseID);
	} 
    updateCategories();
}
function updateCategories()
{

	$args = array(
        'taxonomy'   => "product_cat",
        'hide_empty' => 0
	);
	$product_categories = get_terms($args);

    foreach ($product_categories as $wooCats)
    {
        if($wooCats->term_id != 15){
        $args = array(
            'post_type' => 'product',
            'tax_query' => array(
                array(		
                    'taxonomy'      => 'product_cat',
                    'field' => 'term_id', //This is optional, as it defaults to 'term_id'
                    'terms'         => $wooCats->term_id,
                    'operator'      => 'IN' // Possible values are 'IN', 'NOT IN', 'AND'
                )
            )
        );
        
        $productsFromCat = new WP_Query($args);
        $productsCount=0;
        $productsToAdd=[];
       
        foreach( $productsFromCat->posts as $poste ){
    
            if(get_post_meta($poste->ID, 'firebaseID', true)){
                $productsToAdd[] = ["stringValue" => get_post_meta($poste->ID, 'firebaseID', true)];
                $productsCount++;

            }

        }
            
        if($productsCount>0)
        {
            $category_data["name"] = ["stringValue"  => $wooCats->name];
            $category_data["products"] = ["arrayValue"   => montaArray($productsToAdd)];
        
            if(get_term_meta($wooCats->term_id,"firebaseID", true))
            {
                editDataFirebase("categories",get_term_meta($wooCats->term_id,"firebaseID", true),$category_data);
            }
            else
            {
                $newid = saveDataFirebase("categories",$category_data);
                add_term_meta($wooCats->term_id,"firebaseID",$newid);
            }     
        
        }
        else
    
        {

           if(get_term_meta($wooCats->term_id,"firebaseID", true))
            {
                deleteDataFirebase("categories",get_term_meta($wooCats->term_id,"firebaseID", true));
            }

        }
            
        }
        

}


}


function treatProductCategories($product){

	//trata as cats
	$catCount=0;
	foreach($product->get_category_ids() as $cat){
		if($cat <> 15){
			$catCount++;
			$arrCats[] = ["stringValue" => get_term_meta($cat,"firebaseID", true)];
			}
		}
	
	if($catCount > 0){
            $product_data["cats"] = ["arrayValue" => montaArray($arrCats)];
		}
		else{
			$product_data["cats"] = ["nullValue" => null];
		}



	return $product_data["cats"];

}

function productCreateJSON($product_id,$product)
{
    $product_data  = [
		"id_wordpress" => ["integerValue" => $product_id],
		"name" => ["stringValue" => $product->get_name()],
		"status" => ["stringValue" => $product->get_status()],
		"description" => ["stringValue" => $product->get_description()],
		"short_description" => ["stringValue" => $product->get_short_description()],
		"price" => ["doubleValue" => $product->get_price()],
		"stock_quantity" => ["integerValue" => is_null($product->get_stock_quantity()) ? 0 : $product->get_stock_quantity()],
		"weight" => ["stringValue" => $product->get_weight()],
		"length" => ["stringValue" => $product->get_length()],
		"width" => ["stringValue" => $product->get_width()],
		"height" => ["stringValue" => $product->get_height()],
		"menu_order" => ["integerValue" => $product->get_menu_order()]
	];

    if(get_post_meta($product_id, 'has_result', true)){

        $product_data["has_result"] = ["booleanValue" => true];
    }
    else{
        $product_data["has_result"] = ["booleanValue" => false];
    }

    $productLocal = get_post_meta($product_id, 'local', true);

    if($productLocal){

        $product_data["local"] = ["stringValue" => get_post_meta($productLocal, 'firebaseID',  true)];
    }
    else{
        $product_data["local"] = ["nullValue" => null];
    }


    $productPackage = get_post_meta($product_id, 'package_type', true);

    if($productPackage){

        $product_data["package_type"] = ["stringValue" =>  $productPackage];
    }
    else{
        $product_data["package_type"] = ["nullValue" => null];
    }

    $productPackageQuantity = get_post_meta($product_id, 'package_quantity', true);

    if($productPackageQuantity){

        $product_data["package_quantity"] = ["stringValue" =>  $productPackageQuantity];
    }
    else{
        $product_data["package_quantity"] = ["nullValue" => null];
    }
	//trata o caminho absoluto da imagem
	if($product->get_image_id() != "")
	{
		$product_data["image_url"] = ["stringValue" => wp_get_attachment_image_url( $product->get_image_id(), '')];
	}
	else{
        $product_data["image_url"] = ["nullValue" => null];    
    }
    $product_data["image_gallery"] =["nullValue" => null];

    $imageGallery = $product->get_gallery_image_ids();
    $productImages = [];
    foreach($imageGallery as $image)
    {
        $productImages[] = ["stringValue" => wp_get_attachment_image_url($image, '')];
    }
       
    $product_data["image_gallery"] = ["arrayValue" => montaArray($productImages)];

    
    updateCategories();
    
    $product_data["categories"] = treatProductCategories($product);
    
    return $product_data;

}
?>